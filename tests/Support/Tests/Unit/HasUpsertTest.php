<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Item;
use App\Models\ItemAction;
use App\Models\ItemActionAdditional;
use Illuminate\Database\Query\Expression;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use ReflectionException;
use ReflectionMethod;
use Tests\TestCase;

class HasUpsertTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @dataProvider upsertDataProvider
     */
    public function testUpsertFunction(array $testedItems, $conflictColumns, $update, ?string $selectModelClassname, array $returnColumns, string $expected): void
    {
//        $pdo = DB::getPdo();

//        DB::spy();
        Item::create([
            'name' => 'Test',
            'description' => 'Test Description',
        ]);

        $itemActionMock = $this->partialMock(ItemAction::class);

        $upsertFunction = new ReflectionMethod(ItemAction::class, 'upsert');
        $returnedItems = $upsertFunction->invoke(
            $itemActionMock,
            $testedItems,
            $conflictColumns,
            $update,
            $selectModelClassname,
            $returnColumns
        );


//        $returned = $itemActionMock::upsert($testedItems, $conflictColumns, $update, $selectModelClassname, $returnColumns);

//        DB::shouldReceive('getPdo')->once()->andReturn($pdo);
//        dump(DB::shouldReceive('select')->once()->andReturnSelf());

        dd($returnedItems);
//        $this->assertSame($returnedUpdatedString, $expected);
    }

    /**
     * @noinspection SqlDialectInspection
     * @noinspection SqlNoDataSourceInspection
     */
    public function upsertDataProvider(): array
    {
        return [
            [
                [
                    [
                        'itemId' => 1,
                        'actionName' => 'Test',
                        'actionDescription' => 'Test description',
                    ],
                ],
                ['itemId', 'actionName'], // Conflict
                ['actionDescription'], // Update
                null, // Selected model,
                [],
                'INSERT INTO "itemActions" ("actionName", "actionDescription", "updatedAt", "createdAt") VALUES (\'Test\',\'Test description\',NOW(),NOW())',
            ],
//            [
//                [
//                    [
//                        'where' => [
//                            'actionId' => 1,
//                            'actionName' => 'Test',
//                        ],
//                        'upsert' => [
//                            'specialData' => '123456',
//                            'description' => 'Hello',
//                        ],
//                    ],
//                ], ItemActionAdditional::class, 'INSERT INTO "itemActions" ("specialData", "description", "updatedAt", "createdAt") (SELECT \'123456\',\'Hello\',NOW(),NOW() FROM "itemActionAdditional" WHERE "actionId" = 1 AND "actionName" = \'Test\')',
//            ],
        ];
    }

    /**
     * @dataProvider compileInsertDataProvider
     * @throws ReflectionException
     */
    public function testCompileInsertFunction(array $tested, ?string $selectModelClassname, string $expected): void
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
     * @noinspection SqlDialectInspection
     * @noinspection SqlNoDataSourceInspection
     */
    public function compileInsertDataProvider(): array
    {
        return [
            [
                [
                    [
                        'actionName' => 'Test',
                        'actionDescription' => 'Test description',
                    ],
                ], null, 'INSERT INTO "itemActions" ("actionName", "actionDescription", "updatedAt", "createdAt") VALUES (\'Test\',\'Test description\',NOW(),NOW())'
            ],
            [
                [
                    [
                        'where' => [
                            'actionId' => 1,
                            'actionName' => 'Test',
                        ],
                        'upsert' => [
                            'specialData' => '123456',
                            'description' => 'Hello',
                        ],
                    ],
                ], ItemActionAdditional::class, 'INSERT INTO "itemActions" ("specialData", "description", "updatedAt", "createdAt") (SELECT \'123456\',\'Hello\',NOW(),NOW() FROM "itemActionAdditional" WHERE "actionId" = 1 AND "actionName" = \'Test\')',
            ],
        ];
    }

    /**
     * @dataProvider compileUpdateDataProvider
     * @throws ReflectionException
     */
    public function testCompileUpdateFunction(array $tested, string $expected): void
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

    public function compileUpdateDataProvider(): array
    {
        return [
            [[
                'actionDescription',
                'testField' => 1,
            ], ' DO UPDATE SET "actionDescription" = "excluded"."actionDescription", "testField" = ?'],
            [[
                'actionDescription' => 'Test 123',
                'testField',
            ], ' DO UPDATE SET "actionDescription" = ?, "testField" = "excluded"."testField"'],
        ];
    }

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