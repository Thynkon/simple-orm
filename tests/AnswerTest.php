<?php

namespace Thynkon\SimpleOrm\Test;

require_once(__DIR__ . '/../vendor/autoload.php');
require_once('.env.php');

use ByJG\DbMigration\Exception\DatabaseDoesNotRegistered;
use ByJG\DbMigration\Exception\DatabaseIsIncompleteException;
use ByJG\DbMigration\Exception\DatabaseNotVersionedException;
use ByJG\DbMigration\Exception\InvalidMigrationFile;
use ByJG\DbMigration\Exception\OldVersionSchemaException;
use ByJG\DbMigration\Migration;
use ByJG\Util\Uri;
use PHPUnit\Framework\TestCase;
use Thynkon\SimpleOrm\Test\models\Answer;

class AnswerTest extends TestCase
{
    private Migration $migration;

    /**
     * @throws InvalidMigrationFile
     */
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $connectionUri = new Uri(sprintf('mysql://%s:%s@localhost/looper', USERNAME, PASSWORD));

        // Create the Migration instance
        $this->migration = new Migration($connectionUri, '.');

        // Register the Database or Databases can handle that URI:
        $this->migration->registerDatabase('mysql', \ByJG\DbMigration\Database\MySqlDatabase::class);
        $this->migration->registerDatabase('maria', \ByJG\DbMigration\Database\MySqlDatabase::class);

        // Add a callback progress function to receive info from the execution
        $this->migration->addCallbackProgress(function ($action, $currentVersion, $fileInfo) {
            echo "$action, $currentVersion, ${fileInfo['description']}\n";
        });
    }

    /**
     * @throws DatabaseDoesNotRegistered
     * @throws DatabaseNotVersionedException
     * @throws InvalidMigrationFile
     * @throws DatabaseIsIncompleteException
     * @throws OldVersionSchemaException
     */
    public function setUp(): void
    {
        $this->migration->reset();
        $this->migration->up(1);
    }

    public function testAll()
    {
      $this->assertCount(4, Answer::all());
    }

    public function testFind()
    {
        $this->assertEquals(
            "Answer to question2",
            Answer::find(2)->value
        );
        $this->assertNotEquals(
            "Answer to question3",
            Answer::find(2)->value
        );

    }

    public  function testWhere()
    {
        $this->assertEquals(
            1,
            Answer::where("id", 1)->get()->id
        );

        $this->assertInstanceOf(Answer::class, Answer::where("id", 1)->get());
    }

    /**
     * @covers $answer->create()
     */
    public function testCreate()
    {
        $answer = new Answer();
        $answer->value = "My answer to question 1";
        $answer->question_id = 1;
        $answer->date = date('Y-m-d H:i:s');
        $this->assertTrue($answer->create());
        // there is no way to check if an answer is unique
        // that is why I do not test if $answer->create() returns false
    }

    /**
     * @throws \ReflectionException
     */
    public function testSave()
    {
        $answer = Answer::find(1);
        $answer->value = "Answer1";
        $answer->save();

        $this->assertEquals(
            "Answer1",
            Answer::find(1)->value
        );

        // TODO test id update (try to set id to null or 0)
    }

    /**
     * @throws \ReflectionException
     */
    public function testDelete()
    {
        $answer = Answer::find(1);
        $answer->delete();

        $this->expectException(\Exception::class);
        Answer::find(1);

        $this->expectException(\Exception::class);
        Answer::find(5)->delete();
    }
}
