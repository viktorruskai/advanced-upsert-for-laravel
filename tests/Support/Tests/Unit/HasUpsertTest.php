<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Models\ItemAction;
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
    private $item;

    public function setUp(): void
    {
        $this->hasUpsertTrait = $this->getObjectForTrait(HasUpsert::class);
        $this->item = $this->getMockClass(ItemAction::class);

        parent::setUp();
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
            $this->item,
            [$items],
        );

        dd('s', $returnedItems);
    }
}
