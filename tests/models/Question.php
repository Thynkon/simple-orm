<?php
namespace Thynkon\SimpleOrm\Test\models;

use Thynkon\SimpleOrm\Model;

class Question extends Model
{
    static protected string $table = "question";
    protected string $primaryKey = "id";
    public int $id;
    public string $label;
    public int $question_type_id;
    public int $quiz_id;
}