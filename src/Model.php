<?php

namespace Thynkon\SimpleOrm;

use ReflectionClass;
use ReflectionProperty;
use Thynkon\SimpleOrm\database\Connector;
use Thynkon\SimpleOrm\QueryBuilder;

class Model
{
    protected Connector $connector;
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
        return (new QueryBuilder())->from(static::$table)->get(true, static::class);
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

        $connector = Connector::getInstance();

        try {
            $connector->execute(
                (new QueryBuilder())
                    ->from(static::$table)
                    ->insert($objectProperties),
                $objectProperties,
            );

            $this->$primaryKey = $connector->getConnection()->lastInsertId();

            return true;
        } catch (\PDOException $exception) {
            // return false on duplicate entry
            // print exception message for debug purposes
            echo $exception->getMessage();
            return false;
        }
    }

    /**
     * @throws \ReflectionException
     */
    public static function find(int $id): null|Model
    {
        try {
            return (new QueryBuilder())
                ->from(static::$table)
                ->where("id", $id)
                ->get(class: static::class
                );
        } catch (\Exception $exception) {
            // for debug purposes
            echo $exception->getMessage();
            return null;
        }
    }

    public static function where($column, $value): QueryBuilder
    {
        return (new QueryBuilder())->from(static::$table)->where($column, $value);
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
            unset($objectProperties[$primaryKey]);
        }

        $connector = Connector::getInstance();

        try {
            $connector->execute(
                (new QueryBuilder())
                    ->from(static::$table)
                    ->update($objectProperties)
                    ->where("id", $id),
                $objectProperties,
            );

            return true;
        } catch (\PDOException $exception) {
            return false;
        }
    }

    public function delete(): bool
    {
        return self::destroy($this->id);
    }

    static public function destroy($id): bool
    {
        $connector = Connector::getInstance();

        try {
            return $connector->execute(
                (new QueryBuilder())->from(static::$table)->delete()->where("id", $id),
                [],
            );
        } catch (\PDOException $exception) {
            // return false on duplicate entry
            // print exception message for debug purposes
            echo $exception->getMessage();
            return false;
        }
    }
}
