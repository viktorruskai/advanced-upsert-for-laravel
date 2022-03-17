<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Models\ItemAction;
use Illuminate\Database\Query\Expression;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use ReflectionException;
use ReflectionMethod;
use Tests\TestCase;

class HasUpsertTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @throws ReflectionException
     */
    public function testParseValues(): void
    {
        $itemActionMock = $this->partialMock(ItemAction::class);

        $values = [
            'itemActionId' => '*',
            'specialData' => 'test123',
        ];

        $checkForTimestampsReflection = new ReflectionMethod(ItemAction::class, 'parseValues');
        $checkForTimestampsReflection->setAccessible(true);

        $returnedParsedValues = $checkForTimestampsReflection->invoke(
            $itemActionMock,
            $values
        );
dump($returnedParsedValues);
//        $this->assertSame('"itemId" = 1 AND "actionName" = \'test\'', $returnedParsedValues);
    }

    /**
     * @throws ReflectionException
     */
    public function testParseWhereConditions(): void
    {
        $itemActionMock = $this->partialMock(ItemAction::class);

        $wheres = [
            'itemId' => 1,
            'actionName' => 'test',
        ];

        $checkForTimestampsReflection = new ReflectionMethod(ItemAction::class, 'parseWheres');
        $checkForTimestampsReflection->setAccessible(true);

        $returnedParsedConditions = $checkForTimestampsReflection->invoke(
            $itemActionMock,
            $wheres,
            $itemActionMock::getConnectionResolver()->connection()->getQueryGrammar()
        );

        $this->assertSame('"itemId" = 1 AND "actionName" = \'test\'', $returnedParsedConditions);
    }

    /**
     * @param mixed $tested
     * @param mixed $expected
     *
     * @dataProvider valueDataProvider
     * @throws ReflectionException
     */
    public function testParseValue($tested, $expected): void
    {
        $itemActionMock = $this->partialMock(ItemAction::class);

        $checkForTimestampsReflection = new ReflectionMethod(ItemAction::class, 'parseValues');
        $checkForTimestampsReflection->setAccessible(true);

        $returnedParsedValue = $checkForTimestampsReflection->invoke(
            $itemActionMock,
            $tested,
        );

        $this->assertSame($returnedParsedValue, $expected);
    }

    public function valueDataProvider(): array
    {
        return [
            [0.5, '0.5'],
            [null, ''],
            ['test', '\'test\''],
            [new Expression('NOW()'), '\'NOW()\''],
        ];
    }

    /**
     * @throws ReflectionException
     */
    public function testWrapValues(): void
    {
        $itemActionMock = $this->partialMock(ItemAction::class);

        $value = 'test123';

        $checkForTimestampsReflection = new ReflectionMethod(ItemAction::class, 'wrapValue');
        $checkForTimestampsReflection->setAccessible(true);

        $returnedWrappedValue = $checkForTimestampsReflection->invoke(
            $itemActionMock,
            $value,
        );

        $this->assertSame('"' . $value . '"', $returnedWrappedValue);
    }

    /**
     * @throws ReflectionException
     */
    public function testCheckIfTimestampsAreAddedIntoItems(): void
    {
        $itemActionMock = $this->partialMock(ItemAction::class);

        $items = [
            'actionName' => 'Test',
            'actionDescription' => 'Test description',
        ];

        $checkForTimestampsReflection = new ReflectionMethod(ItemAction::class, 'checkForTimestamps');
        $checkForTimestampsReflection->setAccessible(true);

        $returnedItems = $checkForTimestampsReflection->invoke(
            $itemActionMock,
            [$items],
        );

        $this->assertInstanceOf(Expression::class, $returnedItems[ItemAction::UPDATED_AT]);
        $this->assertInstanceOf(Expression::class, $returnedItems[ItemAction::CREATED_AT]);
        $this->assertSame('NOW()', $returnedItems[ItemAction::UPDATED_AT]->getValue());
        $this->assertSame('NOW()', $returnedItems[ItemAction::CREATED_AT]->getValue());
    }
}