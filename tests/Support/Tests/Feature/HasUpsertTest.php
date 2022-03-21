<?php
/**
 * @noinspection PhpIllegalPsrClassPathInspection
 * @noinspection PhpUndefinedClassInspection
 */
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Item;
use App\Models\ItemAction;
use App\Models\ItemActionAdditional;
use CreateItemActionsTable;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\TestCase;

/**
 * @mixin BaseTestCase
 */
class HasUpsertTest extends TestCase
{
    use DatabaseMigrations;

    public function testBasicUpsert(): void
    {
        $item = Item::create([
            'name' => 'Test',
            'description' => 'Test description',
        ]);

        $itemActions = ItemAction::factory()->count(20)->make([
            'itemId' => $item->getKey(),
        ])->toArray();

        ItemAction::upsert($itemActions, ['itemId', 'actionName'], ['actionDescription', 'actionValue']);

        $itemActionsFromDatabase = ItemAction::where('itemId', 1)
            ->select(['itemId', 'actionName', 'actionDescription', 'actionValue'])
            ->limit(-1)
            ->get()
            ->map(static function ($item) {
                $item['actionValue'] = (int)$item['actionValue'];

                return $item;
            })
            ->toArray();

        $this->assertEqualsCanonicalizing($itemActions, $itemActionsFromDatabase);
    }

    public function testBasicUpsertWithConstraint(): void
    {
        $item = Item::create([
            'name' => 'Test',
            'description' => 'Test',
        ]);

        $itemActions = ItemAction::factory()->count(20)->make([
            'itemId' => $item->getKey(),
        ])->toArray();

        ItemAction::upsert($itemActions, CreateItemActionsTable::CUSTOM_UNIQUE_KEY_FOR_ITEM_ACTIONS, ['actionDescription', 'actionValue']);

        $itemActionsFromDatabase = ItemAction::where('itemId', 1)
            ->select(['itemId', 'actionName', 'actionDescription', 'actionValue'])
            ->limit(-1)
            ->get()
            ->map(static function ($item) {
                $item['actionValue'] = (int)$item['actionValue'];

                return $item;
            })
            ->toArray();

        $this->assertEqualsCanonicalizing($itemActions, $itemActionsFromDatabase);
    }

    public function testUpsertWithSelectingForeignKey(): void
    {
        // Prepare data
        $item = Item::create([
            'name' => 'Test',
            'description' => 'Test description',
        ]);

        $itemActions = ItemAction::factory()
            ->count(20)
            ->make([
                'itemId' => $item->getKey(),
            ])
            ->map(function ($itemAction) {
                $itemAction['additionalData'] = ItemActionAdditional::factory()
                    ->count(10)
                    ->make()
                    ->toArray();

                return $itemAction;
            })
            ->toArray();

        // Process (parse) data
        $additionalData = [];

        foreach ($itemActions as $itemAction) {
            if (!isset($itemAction['additionalData'])) {
                continue;
            }

            foreach ($itemAction['additionalData'] as $additionalDataFromItemAction) {
                $additionalData[] = [
                    'where' => [
                        'itemId' => $item->getKey(),
                        'actionName' => $itemAction['actionName'],
                    ],
                    'upsert' => [
                        'itemActionId' => '*',
                        'specialData' => $additionalDataFromItemAction['specialData'],
                        'description' => $additionalDataFromItemAction['description'],
                    ],
                ];
            }
        }

        // `additionalData` must be unset
        $itemActions = array_map(static function ($itemAction) {
            unset($itemAction['additionalData']);
            return $itemAction;
        }, $itemActions);

        // Upsert
        ItemAction::upsert($itemActions, ['itemId', 'actionName'], ['actionDescription', 'actionValue']);
        ItemActionAdditional::upsert($additionalData, ['itemActionId', 'specialData'], ['description'], ItemAction::class);

        $allItemsInItemActionAdditional = ItemActionAdditional::select(['specialData', 'description'])
            ->limit(-1)
            ->get()
            ->toArray();

        $selectOnlyComparableColumns = array_map(static function ($data) {
            return [
                'specialData' => $data['upsert']['specialData'],
                'description' => $data['upsert']['description'],
            ];
        }, $additionalData);

        $this->assertEqualsCanonicalizing($selectOnlyComparableColumns, $allItemsInItemActionAdditional);
    }

    public function testUpsertWithSelectingForeignKeyAndReturnSomeColumns(): void
    {
        // Prepare data
        $item = Item::create([
            'name' => 'Item',
            'description' => 'Description',
        ]);

        $itemActions = ItemAction::factory()
            ->count(10)
            ->make([
                'itemId' => $item->getKey(),
            ])
            ->map(function ($itemAction) {
                $itemAction['additionalData'] = ItemActionAdditional::factory()
                    ->count(50)
                    ->make()
                    ->toArray();

                return $itemAction;
            })
            ->toArray();

        // Process (parse) data
        $additionalData = [];

        foreach ($itemActions as $itemAction) {
            if (!isset($itemAction['additionalData'])) {
                continue;
            }

            foreach ($itemAction['additionalData'] as $additionalDataFromItemAction) {
                $additionalData[] = [
                    'where' => [
                        'itemId' => $item->getKey(),
                        'actionName' => $itemAction['actionName'],
                    ],
                    'upsert' => [
                        'itemActionId' => '*',
                        'specialData' => $additionalDataFromItemAction['specialData'],
                        'description' => $additionalDataFromItemAction['description'],
                    ],
                ];
            }
        }

        // `additionalData` must be unset
        $itemActions = array_map(static function ($itemAction) {
            unset($itemAction['additionalData']);
            return $itemAction;
        }, $itemActions);

        // Upsert
        ItemAction::upsert($itemActions, ['itemId', 'actionName'], ['actionDescription', 'actionValue']);

        $allItemActionAdditionalReturnedFromDatabase = ItemActionAdditional::upsert($additionalData, ['itemActionId', 'specialData'], ['description'], ItemAction::class, ['specialData', 'description']);

        // Prepare data to compare
        $allItemActionAdditionalReturnedFromDatabase = array_map(static function ($itemActionAdditionalFromDatabase) {
            return (array)$itemActionAdditionalFromDatabase;
        }, $allItemActionAdditionalReturnedFromDatabase);

        $selectOnlyComparableColumns = array_map(static function ($data) {
            return [
                'specialData' => $data['upsert']['specialData'],
                'description' => $data['upsert']['description'],
            ];
        }, $additionalData);

        $this->assertEqualsCanonicalizing($selectOnlyComparableColumns, $allItemActionAdditionalReturnedFromDatabase);
    }
}
