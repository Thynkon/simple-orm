<?php

namespace Thynkon\SimpleOrm\database;

use PDO;
use Thynkon\SimpleOrm\Singleton;

require_once(".env.php");

class DB extends Singleton
{
    private string $dsn;
    private string $username;
    private string $password;
    private PDO $connection;

    public function __construct(string $dsn = DSN, string $username = USERNAME, string $password = PASSWORD)
    {
        $this->dsn = $dsn;
        $this->username = $username;
        $this->password = $password;
        $this->connection = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    public function selectOne(string $query, array $args, $class = null)
    {
        $statement = $this->connection->prepare($query);
        $result = false;

        if ($statement->execute($args)) {
            try {
                if ($class !== null) {
                    $result = $statement->fetchObject($class);
                } else {
                    $result = $statement->fetch(\PDO::FETCH_ASSOC);
                }

                return $result === false ? null : $result;
            } catch (\PDOException $exception) {
                return null;
            }
        }

        return null;
    }

    public function selectMany(string $query, array $args, $class = null)
    {
        $statement = $this->connection->prepare($query);

        if ($statement->execute($args)) {
            if ($class !== null) {
                return $statement->fetchAll(\PDO::FETCH_CLASS, $class);
            } else {
                return $statement->fetchAll(\PDO::FETCH_ASSOC);
            }
        }

        return false;
    }

    public function insert($query, $args)
    {
        $statement = $this->connection->prepare($query);

        try {
            $statement->execute($args);
            return $this->connection->lastInsertId();
        } catch (\PDOException $exception) {
            echo $exception->getMessage();
            return false;
        }
    }

    public function execute(string $query, array $args): int
    {
        $statement = $this->connection->prepare($query);

        $statement->execute($args);
        return $statement->rowCount();
    }
}