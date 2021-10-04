<?php

namespace Thynkon\SimpleOrm;

use PDOException;
use ReflectionException;
use Thynkon\SimpleOrm\database\Connector;
use Thynkon\SimpleOrm\database\Query;
use Thynkon\SimpleOrm\database\sql\DML;
use Thynkon\SimpleOrm\database\sql\DQL;

/**
 * Class QueryBuilder
 */
class QueryBuilder
{
    /**
     * @var array<string>
     */
    private $fields = [];

    /**
     * @var array<string>
     */
    private $conditions = [];

    /**
     * @var array<string>
     */
    private $from = [];

    private $order_by = [];

    private $limit = 0;

    private $update = [];

    private $insert;

    // select,update,etc...
    private $queryCommand;
    private $queryMode;

    public function __construct()
    {
        $this->fields = ["*"];
        $this->queryMode = Query::DQL;
        $this->queryCommand = DQL::SELECT;
    }

    public function __toString(): string
    {
        $where = '';

        if ($this->conditions === []) {
            $where = '';
        } else {
            $where .= ' WHERE ' . $this->conditions[0] . '=' . $this->conditions[1];
            if (count($this->conditions) > 2) {
                for ($i = 2; $i = count($this->conditions); $i += 2) {
                    $where .= ' AND ' . $this->conditions[$i] . '=' . $this->conditions[$i + 1];
                }
            }
        }

        $string = "";

        switch ($this->queryMode) {
            case Query::DQL:
                if ($this->queryCommand === DQL::SELECT) {
                    $order_by = $this->order_by === [] ? '' : ' ORDER BY ' . implode(', ', $this->order_by);
                    $limit = $this->limit === 0 ? '' : ' LIMIT '. $this->limit;

                    $string = 'SELECT ' . implode(', ', $this->fields)
                        . ' FROM ' . implode(', ', $this->from)
                        . $where
                        . $order_by
                        . $limit
                        . ';';
                }
                break;

            case Query::DML:
                switch ($this->queryCommand) {
                    case DML::INSERT:
                        $string = 'INSERT INTO ' . $this->from[0] . ' ' . $this->insert
                            . ';';
                        break;
                    case DML::UPDATE:
                        $string = 'UPDATE ' . $this->from[0] . ' ' . $this->update
                            . $where
                            . ';';
                        break;

                    case DML::DELETE:
                        $string = 'DELETE FROM ' . $this->from[0] . ' ' . $where . ';';
                        break;

                    case DML::LOCK:
                        break;
                    case DML::CALL:
                        break;
                    case DML::EXPLAIN_PLAIN:
                        break;

                    default:
                        break;
                }
                var_dump($string);
                break;

            case Query::DDL:
                break;
        }

        return $string;
    }

    public function select(string ...$select): self
    {
        $this->queryMode = Query::DQL;
        $this->queryCommand = DQL::SELECT;

        $this->fields = $select;
        return $this;
    }

    public function where(string ...$where): self
    {
        foreach ($where as $arg) {
            $this->conditions[] = $arg;
        }
        return $this;
    }

    public function from(string $table, ?string $alias = null): self
    {
        if ($alias === null) {
            $this->from[] = $table;
        } else {
            $this->from[] = "${table} AS ${alias}";
        }
        return $this;
    }

    public function orderBy(string ...$fields): self
    {
        if (count($fields) === 1) {
            $this->order_by[] = $fields[0];
        } else {
            $this->order_by = $fields;
        }

        return $this;
    }

    public function limit(int $limit)
    {
        if ($limit !== null) {
            $this->limit = $limit;
        }

        return $this;
    }

    public function update($fields)
    {
        $this->queryMode = Query::DML;
        $this->queryCommand = DML::UPDATE;

        $this->update = "SET ";

        foreach ($fields as $column => $value) {
            $this->update .= "$column=:$column,";
        }
        $this->update = substr($this->update, 0, -1);

        return $this;
    }

    public function insert($fields)
    {
        $this->queryMode = Query::DML;
        $this->queryCommand = DML::INSERT;

        $this->insert = "SET ";

        foreach ($fields as $column => $value) {
            $this->insert .= "$column=:$column,";
        }
        $this->insert = substr($this->insert, 0, -1);

        return $this;
    }

    public function delete()
    {
        $this->queryMode = Query::DML;
        $this->queryCommand = DML::DELETE;

        return $this;
    }

    /**
     * @param bool $multiple_rows
     * @param null $class
     * @return array|Object
     * @throws ReflectionException
     * @throws PDOException
     */
    public function get(bool $multiple_rows = false, $class = null): array|Model
    {
        $table = $this->from[0];
        if ($class === null) {
            // without the namespace, composer autoloader
            // won't be able to find the class
            $class = "App\\models\\";
            if (str_contains($table, '_')) {
                $class .= CustomString::fromSnakeToCamel($table);
            } else {
                $class .= ucfirst($table);
            }
        }

        $connector = Connector::getInstance();
        $connection = $connector->getConnection();
        $statement = $connection->prepare($this->__toString());

        var_dump($this->__toString());
        $function = $multiple_rows === true ? "fetchAll" : "fetch";

        if ($statement->execute()) {
            // convert array to Object or
            // array of arrays to array of objects
            $hydrator = new Hydrator();
            // if select function returns an empty resultset,
            // pdo does not throw an exeception (since the query was
            // successfully performed)
            $result = $statement->$function(\PDO::FETCH_ASSOC);
            if ($result === false) {
                throw new \Exception("Object not found in database");
            }

            return $hydrator->hydrate($class, $result);
        }
    }
}
