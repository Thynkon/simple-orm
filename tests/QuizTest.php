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
use Thynkon\SimpleOrm\Test\models\QuestionType;
use Thynkon\SimpleOrm\Test\models\Quiz;

class QuizTest extends TestCase
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
      $this->assertCount(3, Quiz::all());
    }

    public function testFind()
    {
        $this->assertEquals(
            "Answering form 123",
            Quiz::find(2)->title
        );
        $this->assertNotEquals(
            "Building form 123",
            Quiz::find(2)->title
        );

    }

    public function testWhere()
    {
        $this->assertEquals(1,count(Quiz::where("title","Building form 123")));
    }

    /**
     * @covers $quiz->create()
     */
    public function testCreate()
    {
        $quiz = new Quiz();
        $quiz->title = "QuizToto";
        $quiz->is_public = 1;
        $quiz->quiz_state_id = 1;
        $this->assertTrue($quiz->create());
        $this->assertFalse($quiz->create());
    }

    /**
     * @throws \ReflectionException
     */
    public function testSave()
    {
        $quiz = Quiz::find(2);
        $quiz->title = "QuizTest";
        $quiz->save();

        $this->assertEquals(
            "QuizTest",
            Quiz::find(2)->title
        );

        // test boolean value update
        $quiz->is_public = 0;
        $quiz->save();

        $this->assertEquals(
            false,
            Quiz::find(2)->is_public
        );

        // TODO test id update (try to set id to null or 0)
    }

    /**
     * @throws \ReflectionException
     */
    public function testDelete()
    {
        $quiz = Quiz::find(1);
        $quiz->delete();
        $this->assertNull(Quiz::find(1));

        $quiz = Quiz::find(3);
        $quiz->delete();
        $this->assertNull(Quiz::find(5));
    }
}
