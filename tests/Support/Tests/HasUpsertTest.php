<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Item;
use App\Models\ItemAction;
use App\Models\ItemActionAdditional;
use Tests\TestCase;

class HasUpsertTest extends TestCase
{

    public function testBasicUpsert(): void
    {
        $item = Item::create([
            'name' => 'Test',
            'description' => 'Test description',
        ]);

        $itemActions = ItemAction::factory()->count(20)->make([
            'itemId' => $item->getKey(),
        ])->toArray();

//        $itemActionsFromDatabase = ItemAction::upsert($itemActions, ['itemId', 'actionName'], ['actionDescription', 'actionValue'], null, ['itemId', 'actionName', 'actionDescription', 'actionValue']);
        ItemAction::upsert($itemActions, ['itemId', 'actionName'], ['actionDescription', 'actionValue']);

//        $itemActionsFromDatabase = array_map(static function ($item) {
//            $item = (array)$item;
//            $item['actionValue'] = (int)$item['actionValue'];
//            return $item;
//        }, $itemActionsFromDatabase);

//        $this->assertEqualsCanonicalizing($itemActions, $itemActionsFromDatabase);


        $itemActionsFromDatabase = ItemAction::where('itemId', 1)
            ->select(['itemId', 'actionName', 'actionDescription', 'actionValue'])
            ->limit(-1)
            ->get()
//            ->map(static function ($item) {
//                $item['actionValue'] = (int)$item['actionValue'];
//
//                return $item;
//            })
            ->toArray();

        $this->assertEqualsCanonicalizing($itemActions, $itemActionsFromDatabase);
    }

    public function testAdvancedUpsert(): void
    {
        $this->markTestSkipped('skipped');

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
                $itemAction = $itemAction->toArray();

                $itemAction['additionalData'] = ItemActionAdditional::factory()
                    ->count(10)
                    ->make();
            })
            ->toArray();

        // Process data
        $additionalData = [];

        foreach ($itemActions as $itemAction) {
            if (!isset($itemAction['additionalData'])) {
                continue;
            }

            foreach ($itemAction['additionalData'] as $additionalData) {
                $additionalData[] = [
                    'where' => [
                        'itemId' => $item->getKey(),
                        'actionName' => $itemAction['actionName'],
                    ],
                    'upsert' => [
                        'itemActionId' => '*',
                        'specialData' => $additionalData['specialData'],
                        'description' => $additionalData['description'],
                    ],
                ];
            }
        }

        // Todo: Do not forget to remove `additionalData`

        ItemAction::upsert($itemActions, ['itemId', 'actionName'], ['actionDescription', 'actionValue']);

        $specialData = ItemActionAdditional::upsert($additionalData, ['itemActionId', 'specialData'], ['description'], ItemAction::class, ['specialData']);

        dump($specialData);

        $itemActionsFromDatabase = ItemAction::where('itemId', 1)
            ->select(['itemId', 'actionName', 'actionDescription', 'actionValue'])
            ->limit(-1)
            ->get()
            ->toArray();

        $this->assertEqualsCanonicalizing($itemActions, $itemActionsFromDatabase);
    }
}
