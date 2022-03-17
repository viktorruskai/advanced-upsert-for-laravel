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

    private $itemActionMock;

    public function setUp(): void
    {
        $this->itemActionMock = $this->partialMock(ItemAction::class);

        parent::setUp();
    }

    /**
     * @throws ReflectionException
     */
    public function testWrapValues()
    {
        $value = 'test123';

        $checkForTimestampsReflection = new ReflectionMethod(
            ItemAction::class,
            'wrapValue'
        );
        $checkForTimestampsReflection->setAccessible(true);
        $returnedItems = $checkForTimestampsReflection->invoke(
            $this->itemActionMock,
            [$value],
        );

dump($returnedItems);
    }

    /**
     * @throws ReflectionException
     */
    public function testCheckIfTimestampsAreAddedIntoItems()
    {
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
            $this->itemActionMock,
            [$items],
        );

        $this->assertInstanceOf(Expression::class, $returnedItems[ItemAction::UPDATED_AT]);
        $this->assertInstanceOf(Expression::class, $returnedItems[ItemAction::CREATED_AT]);
        $this->assertSame('NOW()', $returnedItems[ItemAction::UPDATED_AT]->getValue());
        $this->assertSame('NOW()', $returnedItems[ItemAction::CREATED_AT]->getValue());
    }
}