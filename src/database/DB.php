<?php

namespace Thynkon\SimpleOrm\database;

use Exception;
use PDO;
use PDOException;
use Thynkon\SimpleOrm\Model;

require_once(".env.php");

class DB
{
    private ?PDO $connection = null;
    private static ?DB $instance = null;

    private function __construct() {}

    public static function getInstance()
    {
        if (self::$instance === null){
            $className = static::class;
            self::$instance = new $className;
        }

        return self::$instance;
    }

    private function getConnection(): PDO
    {
        if ($this->connection === null) {
            $this->connection = new PDO(DSN, USERNAME, PASSWORD);
        }

        return $this->connection;
    }

    public function selectOne(string $query, array $args, $class = null): null|Model|array
    {
        $statement = $this->getConnection()->prepare($query);
        $result = false;

        if ($statement->execute($args)) {
            try {
                if ($class !== null) {
                    $result = $statement->fetchObject($class);
                } else {
                    $result = $statement->fetch(PDO::FETCH_ASSOC);
                }

                return $result === false ? null : $result;
            } catch (PDOException $exception) {
                return null;
            }
        }

        return null;
    }

    public function selectMany(string $query, array $args, $class = null): bool|array
    {
        $statement = $this->getConnection()->prepare($query);

        if ($statement->execute($args)) {
            if ($class !== null) {
                return $statement->fetchAll(PDO::FETCH_CLASS, $class);
            } else {
                return $statement->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        return false;
    }

    public function insert($query, $args): bool
    {
        $statement = $this->getConnection()->prepare($query);

        try {
            $statement->execute($args);
            return $this->connection->lastInsertId();
        } catch (PDOException $exception) {
            echo $exception->getMessage();
            return false;
        }
    }

    public function execute(string $query, array $args): int
    {
        $statement = $this->getConnection()->prepare($query);

        $statement->execute($args);
        return $statement->rowCount();
    }

    /**
     * Singletons should not be cloneable.
     */
    protected function __clone() { }

    /**
     * Singletons should not be restorable from strings.
     * @throws Exception
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize a singleton.");
    }
}