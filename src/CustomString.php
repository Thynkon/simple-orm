<?php

namespace Thynkon\SimpleOrm;

class CustomString
{
    private static function toCamelCase($string, $needle, $capitalizeFirstCharacter = false)
    {
        $str = str_replace(' ', '', ucwords(str_replace($needle, ' ', $string)));

        if (!$capitalizeFirstCharacter) {
            $str[0] = strtolower($str[0]);
        }

        return $str;
    }

    public static function fromSnakeToCamel($string): string
    {
        return static::toCamelCase($string, '_');
    }

}