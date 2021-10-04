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
use Thynkon\SimpleOrm\Test\models\Question;

class QuestionTest extends TestCase
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
      $this->assertCount(5, Question::all());
    }

    public function testFind()
    {
        $this->assertEquals(
            "Question2",
            Question::find(2)->label
        );
        $this->assertNotEquals(
            "Question1",
            Question::find(2)->label
        );

    }

    public  function testWhere()
    {
        $this->assertEquals(
            1,
            Question::where("id", 1)->get()->id
        );

        $this->assertInstanceOf(Question::class, Question::where("id", 1)->get());
    }

    /**
     * @throws \ReflectionException
     */
    public function testSave()
    {
        $question = Question::find(1);
        $question->label = "Question1";
        $question->save();

        $this->assertEquals(
            "Question1",
            Question::find(1)->label
        );

        // TODO test id update (try to set id to null or 0)
    }

    /**
     * @throws \ReflectionException
     */
    public function testDelete()
    {
        $question = Question::find(1);
        $question->delete();

        $this->expectException(\Exception::class);
        Question::find(1);

        $this->expectException(\Exception::class);
        Question::find(5)->delete();
    }

}
