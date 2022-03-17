<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Models\ItemAction;
use Illuminate\Database\Query\Expression;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use ReflectionException;
use ReflectionMethod;
use Tests\TestCase;
use ViktorRuskai\AdvancedUpsert\HasUpsert;

/**
 * @method getObjectForTrait(string $class) @see \PHPUnit\Framework\TestCase
 */
class HasUpsertTest extends TestCase
{
    use DatabaseMigrations;

    private $hasUpsertTrait;

    public function setUp(): void
    {
        $this->hasUpsertTrait = $this->getObjectForTrait(HasUpsert::class);

        parent::setUp();
    }

    /**
     * @throws ReflectionException
     */
    public function testCheckIfTimestampsAreAddedIntoItems()
    {
        $mock = $this->partialMock(ItemAction::class);

//        $mock = \Mockery::namedMock('self', 'ClassConstantStub');

        $items = [
            'actionName' => 'Test',
            'actionDescription' => 'Test description',
        ];

        $checkForTimestampsReflection = new ReflectionMethod(
            ItemAction::class,
            'checkForTimestamps'
        );
        $checkForTimestampsReflection->setAccessible(true);
        $returnedItems = $checkForTimestampsReflection->invoke(
            $mock,
            [$items],
        );

        $this->assertInstanceOf(Expression::class, $returnedItems[ItemAction::UPDATED_AT]);
        $this->assertInstanceOf(Expression::class, $returnedItems[ItemAction::CREATED_AT]);
        dump($returnedItems);
    }
}
//class ClassConstantStub
//{
//    public const UPDATED_AT = 'updatedAt';
//    public const CREATED_AT = 'createdAt';
//}