<?php
declare(strict_types=1);

namespace Tests\Unit;

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

    /**
     * @throws ReflectionException
     */
    public function testCheckIfTimestampsAreAddedIntoItems()
    {
        $hasUpsertTrait = $this->getObjectForTrait(HasUpsert::class);

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
            $hasUpsertTrait,
            [$items],
        );

        dd($returnedItems);
    }
}
