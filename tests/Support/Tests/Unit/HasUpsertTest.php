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
        \Mockery::getConfiguration()->setConstantsMap([
            'HasUpsert' => [
                'UPDATED_AT' => 'updatedAt',
                'CREATED_AT' => 'createdAt',
            ]
        ]);

        $items = [
            'actionName' => 'Test',
            'actionDescription' => 'Test description',
        ];

        $checkForTimestampsReflection = new ReflectionMethod(
            HasUpsert::class,
            'checkForTimestamps'
        );
        $checkForTimestampsReflection->setAccessible(true);
        $returnedItems = $checkForTimestampsReflection->invoke(
            $this->hasUpsertTrait,
            [$items],
        );

        dd($returnedItems);
    }
}
