<?php

namespace Thynkon\SimpleOrm\database;

use PDO;
use Thynkon\SimpleOrm\Singleton;

require_once(".env.php");

class Connector extends Singleton
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

    public function execute(string $query, array $args): int
    {
        $statement = $this->connection->prepare($query);

        $statement->execute($args);
        return $statement->rowCount();
    }
}