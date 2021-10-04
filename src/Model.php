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
        // get list of properties and populate them using the 'args' array
        $reflection = new ReflectionClass($this);
        $vars = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

        foreach ($vars as $attribute) {
            $this->{$attribute->getName()} = $args[$attribute->getName()];
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

    /**
     * @throws \ReflectionException
     */
    public static function find(int $id): Model
    {
        return (new QueryBuilder())
            ->from(static::$table)
            ->where("id", $id)
            ->get(class: static::class
        );
    }

    public static function where($column, $value): QueryBuilder
    {
        return (new QueryBuilder())->from(static::$table)->where($column, $value);
    }

    public function save()
    {
        $objectProperties = get_object_vars($this);

        $isPrimaryKeyInArray = array_key_exists("primaryKey", $objectProperties);
        $isIdInArray = array_key_exists($objectProperties["primaryKey"], $objectProperties);

        $primaryKey = $objectProperties["primaryKey"];
        $id = $objectProperties[$primaryKey];

        // remove primaryKey and id from final sql query
        if ($isPrimaryKeyInArray === true || $isIdInArray === true) {
            unset($objectProperties["primaryKey"]);
            unset($objectProperties[$primaryKey]);
        }

        $connector = Connector::getInstance();

        return $connector->execute(
            (new QueryBuilder())
                ->from(static::$table)
                ->update($objectProperties)
                ->where("id", $id),
            $objectProperties,
        );
    }

    public function delete(): bool
    {
        return self::destroy($this->id);
    }

    static public function destroy($id): bool
    {
        $connector = Connector::getInstance();

        //return Connector::execute(
        return $connector->execute(
            (new QueryBuilder())->from(static::$table)->delete()->where("id", $id),
            [],
        );
    }
}
