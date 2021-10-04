<?php
namespace Thynkon\SimpleOrm\Test\models;

use Thynkon\SimpleOrm\Model;

class Quiz extends Model
{
    static protected string $table = "quiz";
    protected string $primaryKey = "id";
    public int $id;
    public string $title;
    // int should be boolean but, for no reason, PDO (exec function) converts
    // false to '' (empty string) instead of to 0, (which it does
    // for when value is true!!!!)
    // https://stackoverflow.com/a/64874581
    public int $is_public;
    public int $quiz_state_id;
}