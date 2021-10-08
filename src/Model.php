<?php

namespace Thynkon\SimpleOrm;

use ReflectionClass;
use ReflectionProperty;
use Thynkon\SimpleOrm\database\DB;

class Model
{
    protected DB $connector;
    protected string $primaryKey;

    public function __construct(array $args = [])
    {
        if ($args !== []) {
            // get list of properties and populate them using the 'args' array
            $reflection = new ReflectionClass($this);
            $vars = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

            foreach ($vars as $attribute) {
                if (key_exists($attribute->getName(), $args)) {
                    $this->{$attribute->getName()} = $args[$attribute->getName()];
                }
            }
        }
    }

    public static function make(array $fields = null): null|Model
    {
        // this is the only way we found to call either Quizz or Question constructor
        // from ModelOLD's static function
        $class_name = get_called_class();
        return new $class_name($fields);
    }

    public static function all()
    {
        $query = "SELECT * FROM " . static::$table . ";";
        $database = DB::getInstance();
        return $database->selectMany($query, [], static::class);
    }

    public function create(): bool
    {
        $objectProperties = get_object_vars($this);

        $isPrimaryKeyInArray = array_key_exists("primaryKey", $objectProperties);
        $isIdInArray = array_key_exists($objectProperties["primaryKey"], $objectProperties);

        $primaryKey = isset($objectProperties["primaryKey"]) === true ? $objectProperties["primaryKey"] : false;
        $id = isset($objectProperties[$primaryKey]) === true ? $objectProperties[$primaryKey] : false;

        // remove primaryKey and id from final sql query
        if ($isPrimaryKeyInArray === true || $isIdInArray === true) {
            unset($objectProperties["primaryKey"]);
            unset($objectProperties[$primaryKey]);
        }

        $database = DB::getInstance();
        $query = sprintf("INSERT INTO `%s` SET ", static::$table);
        foreach ($objectProperties as $column => $value) {
            $query .= "$column=:$column,";
        }
        $query = substr($query, 0, -1);

        $lastInsertId = $database->insert($query, $objectProperties);
        if ($lastInsertId === false) {
            return false;
        } else {
            $this->$primaryKey = $lastInsertId;
        }

        return true;
    }

    /**
     * @throws \ReflectionException
     */
    public static function find(int $id): null|Model
    {
        $database = DB::getInstance();
        $query = "SELECT * FROM " . static::$table . " WHERE id = :id;";
        $database = DB::getInstance();

        return $database->selectOne($query, ["id" => $id], static::class);

    }

    public static function where(string $column, mixed $value): array
    {
        $database = DB::getInstance();
        $query = "SELECT * FROM " . static::$table . " WHERE $column = :value;";
        $database = DB::getInstance();

        return $database->selectMany($query, ["value" => $value], static::class);
    }

    public function save(): bool
    {
        $objectProperties = get_object_vars($this);

        $isPrimaryKeyInArray = array_key_exists("primaryKey", $objectProperties);
        $isIdInArray = array_key_exists($objectProperties["primaryKey"], $objectProperties);

        $primaryKey = isset($objectProperties["primaryKey"]) === true ? $objectProperties["primaryKey"] : false;
        $id = isset($objectProperties[$primaryKey]) === true ? $objectProperties[$primaryKey] : false;

        // remove primaryKey and id from final sql query
        if ($isPrimaryKeyInArray === true || $isIdInArray === true) {
            unset($objectProperties["primaryKey"]);
            //unset($objectProperties[$primaryKey]);
        }

        try {
            $database = DB::getInstance();
            $query = sprintf("UPDATE `%s` SET ", static::$table);
            foreach ($objectProperties as $column => $value) {
                $query .= "$column=:$column,";
            }
            $query = substr($query, 0, -1);
            $query .= " WHERE id = :id;";

            return $database->execute($query, $objectProperties);

        } catch (\PDOException $exception) {
            echo "CREATE====>\n";
            echo $exception->getMessage();
            return false;
        }
    }

    public function delete(): bool
    {
        return self::destroy($this->id);
    }

    static public function destroy($id): bool
    {
        try {
            $database = DB::getInstance();
            $query = sprintf("DELETE FROM `%s` WHERE id = :id", static::$table);

            return $database->execute($query, ["id" => $id]);
        } catch (\PDOException $exception) {
            // return false on duplicate entry
            // print exception message for debug purposes
            echo $exception->getMessage();
            return false;
        }
    }
}
