<?php

namespace Thynkon\SimpleOrm\database;

use Exception;
use PDO;
use PDOException;
use Thynkon\SimpleOrm\Model;

class DB
{
    private static ?DB $instance = null;
    private ?PDO $connection = null;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            $className = static::class;
            self::$instance = new $className;
        }

        return self::$instance;
    }

    public function beginTransaction()
    {
        $this->connection->beginTransaction();
    }

    public function commit()
    {
        $this->connection->commit();
    }

    public function rollback()
    {
        $this->connection->rollback();
    }

    public function selectOne(string $query, array $args, $class = null): null|Model|array
    {
        $statement = $this->getConnection()->prepare($query);
        $result = false;

        if ($statement->execute($args)) {
            if ($class !== null) {
                $result = $statement->fetchObject($class);
            } else {
                $result = $statement->fetch(PDO::FETCH_ASSOC);
            }

            return $result === false ? null : $result;
        }
    }

    private function getConnection(): PDO
    {
        if ($this->connection === null) {
            $this->connection = new PDO(DSN, USERNAME, PASSWORD);
        }

        return $this->connection;
    }

    public function selectMany(string $query, array $args, $class = null): null|array
    {
        $statement = $this->getConnection()->prepare($query);
        $result = false;

        if ($statement->execute($args)) {
            if ($class !== null) {
                $result = $statement->fetchAll(PDO::FETCH_CLASS, $class);
            } else {
                $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            }

            return $result === false ? null : $result;
        }
    }

    public function insert($query, $args): int
    {
        $statement = $this->getConnection()->prepare($query);

        $statement->execute($args);
        return $this->connection->lastInsertId();
    }

    public function execute(string $query, array $args): int
    {
        $statement = $this->getConnection()->prepare($query);

        $statement->execute($args);
        return $statement->rowCount();
    }

    /**
     * Singletons should not be restorable from strings.
     * @throws Exception
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize a singleton.");
    }

    /**
     * Singletons should not be cloneable.
     */
    protected function __clone()
    {
    }
}
