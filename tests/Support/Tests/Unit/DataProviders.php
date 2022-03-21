<?php
/**
 * @noinspection PhpIllegalPsrClassPathInspection
 * @noinspection PhpUndefinedClassInspection
 */
declare(strict_types=1);

namespace Tests\Unit;

use App\Models\ItemAction;
use App\Models\ItemActionAdditional;
use Illuminate\Database\Query\Expression;

trait DataProviders
{

    /**
     * @noinspection SqlDialectInspection
     * @noinspection SqlNoDataSourceInspection
     */
    public function upsertDataProvider(): array
    {
        return [
            [
                ItemAction::class,
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
                'INSERT INTO "itemActions" ("itemId", "actionName", "actionDescription", "updatedAt", "createdAt") VALUES (1,\'Test\',\'Test description\',NOW(),NOW()) ON CONFLICT ("itemId", "actionName") DO UPDATE SET "actionDescription" = "excluded"."actionDescription"',
            ],
            [
                ItemAction::class,
                [
                    [
                        'itemId' => 1,
                        'actionName' => 'Test',
                        'actionDescription' => 'Test description',
                    ],
                ],
                'custom_unique_key_for_item_actions', // Conflict
                ['actionDescription'], // Update
                null, // Selected model,
                [],
                'INSERT INTO "itemActions" ("itemId", "actionName", "actionDescription", "updatedAt", "createdAt") VALUES (1,\'Test\',\'Test description\',NOW(),NOW()) ON CONFLICT ON CONSTRAINT custom_unique_key_for_item_actions DO UPDATE SET "actionDescription" = "excluded"."actionDescription"',
            ],
            [
                ItemActionAdditional::class,
                [
                    [
                        'where' => [
                            'itemId' => 1,
                            'actionName' => 'Test',
                        ],
                        'upsert' => [
                            'itemActionId' => '*',
                            'specialData' => '123456',
                            'description' => 'Hello',
                        ],
                    ],
                ],
                ['itemActionId', 'specialData'], // Conflict
                ['description'], // Update
                ItemAction::class, // Selected model
                [],
                'INSERT INTO "itemActionAdditional" ("itemActionId", "specialData", "description", "updatedAt", "createdAt") (SELECT id,\'123456\',\'Hello\',NOW(),NOW() FROM "itemActions" WHERE "itemId" = 1 AND "actionName" = \'Test\') ON CONFLICT ("itemActionId", "specialData") DO UPDATE SET "description" = "excluded"."description"',
            ],
        ];
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

    public function valueDataProvider(): array
    {
        return [
            [0.5, '0.5'],
            [null, ''],
            ['test', '\'test\''],
            [new Expression('NOW()'), '\'NOW()\''],
        ];
    }
}