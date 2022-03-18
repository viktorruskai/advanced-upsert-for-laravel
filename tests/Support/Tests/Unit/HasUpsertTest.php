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
    public function testCompileReturnFunction(): void
    {
        $itemActionMock = $this->partialMock(ItemAction::class);

        $values = [
            'id',
            'actionName',
        ];

        $compileReturnFunction = new ReflectionMethod(ItemAction::class, 'compileReturn');
        $compileReturnFunction->setAccessible(true);

        $returnedString = $compileReturnFunction->invoke(
            $itemActionMock,
            $values
        );

        $this->assertSame(' RETURNING id, actionName', $returnedString);
    }

    /**
     * @throws ReflectionException
     */
    public function testParseValues(): void
    {
        $itemActionMock = $this->partialMock(ItemAction::class);

        $values = [
            '*',
            'test123',
        ];

        $parseValuesFunction = new ReflectionMethod(ItemAction::class, 'parseValues');
        $parseValuesFunction->setAccessible(true);

        $returnedParsedValues = $parseValuesFunction->invoke(
            $itemActionMock,
            $values
        );

        $this->assertSame('id,\'test123\'', $returnedParsedValues);
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

        $parseWheresFunction = new ReflectionMethod(ItemAction::class, 'parseWheres');
        $parseWheresFunction->setAccessible(true);

        $returnedParsedConditions = $parseWheresFunction->invoke(
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

        $parseValuesFunction = new ReflectionMethod(ItemAction::class, 'parseValues');
        $parseValuesFunction->setAccessible(true);

        $returnedParsedValue = $parseValuesFunction->invoke(
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

        $wrapValueFunction = new ReflectionMethod(ItemAction::class, 'wrapValue');
        $wrapValueFunction->setAccessible(true);

        $returnedWrappedValue = $wrapValueFunction->invoke(
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