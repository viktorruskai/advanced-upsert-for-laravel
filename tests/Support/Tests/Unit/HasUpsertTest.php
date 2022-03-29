<?php
/**
 * @noinspection PhpIllegalPsrClassPathInspection
 * @noinspection PhpUndefinedClassInspection
 */
declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Item;
use App\Models\ItemAction;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use ReflectionException;
use ReflectionMethod;
use Tests\TestCase;

/**
 * @mixin BaseTestCase
 */
class HasUpsertTest extends TestCase
{
    use DatabaseMigrations, DataProviders;

    /**
     * Must be tested by inserting row into the DB.
     *
     * @dataProvider upsertDataProvider
     *
     * @throws ReflectionException
     */
    public function testUpsertFunction(string $model, array $testedItems, $conflictColumns, $update, ?string $selectModelClassname, array $returnColumns, string $expected): void
    {
        Item::create([
            'name' => 'Test',
            'description' => 'Test Description',
        ]);

        DB::enableQueryLog();

        $itemActionMock = $this->partialMock($model);

        $upsertFunction = new ReflectionMethod($model, 'upsert');
        $upsertFunction->invoke(
            $itemActionMock,
            $testedItems,
            $conflictColumns,
            $update,
            $selectModelClassname,
            $returnColumns
        );

        $this->assertSame(DB::getQueryLog()[0]['query'] ?? [], $expected);
    }

    /**
     * @throws ReflectionException
     */
    public function testInvalidConflictColumnsInUpsertFunction(): void
    {
        $this->expectException(QueryException::class);

        Item::create([
            'name' => 'Test',
            'description' => 'Test Description',
        ]);

        DB::enableQueryLog();

        $itemActionMock = $this->partialMock(ItemAction::class);

        $upsertFunction = new ReflectionMethod(ItemAction::class, 'upsert');
        $upsertFunction->invoke(
            $itemActionMock,
            [
                [
                    'itemId' => 1,
                    'actionName' => 'Test',
                    'actionDescription' => 'Test description',
                ],
            ],
            ['itemId', 'test'],
            ['description']
        );
    }

    /**
     * @throws ReflectionException
     */
    public function testInvalidConflictConstraintInUpsertFunction(): void
    {
        $this->expectException(QueryException::class);

        Item::create([
            'name' => 'Test',
            'description' => 'Test Description',
        ]);

        DB::enableQueryLog();

        $itemActionMock = $this->partialMock(ItemAction::class);

        $upsertFunction = new ReflectionMethod(ItemAction::class, 'upsert');
        $upsertFunction->invoke(
            $itemActionMock,
            [
                [
                    'itemId' => 1,
                    'actionName' => 'Test',
                    'actionDescription' => 'Test description',
                ],
            ],
            'random_unique_key_name',
            ['description']
        );
    }

    /**
     * @dataProvider compileInsertDataProvider
     * @throws ReflectionException
     */
    public function testCompileInsertFunction($tested, ?string $selectModelClassname, string $expected): void
    {
        $itemActionMock = new ItemAction();

        $compileInsertFunction = new ReflectionMethod(ItemAction::class, 'compileInsert');
        $compileInsertFunction->setAccessible(true);

        $returnedUpdatedString = $compileInsertFunction->invoke(
            $itemActionMock,
            $itemActionMock::getConnectionResolver()->connection()->getQueryGrammar(),
            $itemActionMock::query()->getQuery(),
            $tested,
            $selectModelClassname
        );

        $this->assertSame($returnedUpdatedString, $expected);
    }

    /**
     * @dataProvider compileUpdateDataProvider
     * @throws ReflectionException
     */
    public function testCompileUpdateFunction($tested, string $expected): void
    {
        $itemActionMock = $this->partialMock(ItemAction::class);

        $compileUpdateFunction = new ReflectionMethod(ItemAction::class, 'compileUpdate');
        $compileUpdateFunction->setAccessible(true);

        $returnedUpdatedString = $compileUpdateFunction->invoke(
            $itemActionMock,
            $tested,
            $itemActionMock::getConnectionResolver()->connection()->getQueryGrammar()
        );

        $this->assertSame($returnedUpdatedString, $expected);
    }

    /**
     * @throws ReflectionException
     */
    public function testCompileReturnFunction(): void
    {
        $itemActionMock = $this->partialMock(ItemAction::class);

        $values = collect([
            'id',
            'actionName',
        ]);

        $compileReturnFunction = new ReflectionMethod(ItemAction::class, 'compileReturn');
        $compileReturnFunction->setAccessible(true);

        $returnedString = $compileReturnFunction->invoke(
            $itemActionMock,
            $values,
            $itemActionMock::getConnectionResolver()->connection()->getQueryGrammar()
        );

        $this->assertSame(' RETURNING "id", "actionName"', $returnedString);
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