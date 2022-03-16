<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Item;
use App\Models\ItemAction;
use App\Models\ItemActionAdditional;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

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

    public function testAdvancedUpsert(): void
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
//                $itemAction = $itemAction->toArray();

                $itemAction['additionalData'] = ItemActionAdditional::factory()
                    ->count(10)
                    ->make()
                    ->toArray();

                return $itemAction;
            })
            ->toArray();

        // Process data
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

dump('------', $additionalData, ';;;;;;;;;;;;;;;;');
        ItemAction::upsert($itemActions, ['itemId', 'actionName'], ['actionDescription', 'actionValue']);

        $specialData = ItemActionAdditional::upsert($additionalData, ['itemActionId', 'specialData'], ['description'], ItemAction::class, ['specialData']);

        dump($specialData);

        $allItemsInItemActionAdditional = ItemAction::select(['specialData', 'description'])
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
}
