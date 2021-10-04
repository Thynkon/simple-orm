<?php
namespace Thynkon\SimpleOrm\Test\models;

use Thynkon\SimpleOrm\Model;

class QuestionType extends Model
{
    static protected string $table = "question_type";
    protected string $primaryKey = "id";
    public int $id;
    public string $label;
}