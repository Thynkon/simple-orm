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
use Thynkon\SimpleOrm\Test\models\QuestionType;
use Thynkon\SimpleOrm\Test\models\Quiz;
use Thynkon\SimpleOrm\Test\models\QuizState;

class QuizStateTest extends TestCase
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
      $this->assertCount(3, QuizState::all());
    }

    public function testFind()
    {
        $this->assertEquals(
            "Answering",
            QuizState::find(2)->label
        );
        $this->assertNotEquals(
            "Building",
            QuizState::find(2)->label
        );

    }

    public  function testWhere()
    {
        $this->assertEquals(1,count(QuizState::where("label","Building")));
    }

    /**
     * @covers $quiz_state->create()
     */
    public function testCreate()
    {
        $quiz_state = new QuestionType();
        $quiz_state->label = "QuizState1234";
        $this->assertTrue($quiz_state->create());

        $this->expectException(\PDOException::class);
        $quiz_state->create();
    }

    /**
     * @throws \ReflectionException
     */
    public function testSave()
    {
        $quiz_state = QuizState::find(1);
        $quiz_state->label = "QuizState1";
        $quiz_state->save();

        $this->assertEquals(
            "QuizState1",
            QuizState::find(1)->label
        );

        // TODO test id update (try to set id to null or 0)
    }

    /**
     * @throws \ReflectionException
     */
    public function testDelete()
    {
        $quiz_state = QuizState::find(1);
        $quiz_state->delete();
        $this->assertNull(QuizState::find(1));

        $quiz_state = QuizState::find(2);
        $quiz_state->delete();
        $this->assertNull(QuizState::find(5));
    }

}
