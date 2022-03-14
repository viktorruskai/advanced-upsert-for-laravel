<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Item;
use App\Models\ItemAction;
use Tests\TestCase;

class HasUpsertTest extends TestCase
{

    public function testBasicUpsert(): void
    {
        $item = Item::insert([
            'name' => 'Test',
            'description' => 'Test description',
        ]);

        dump($item);

        $itemActions = ItemAction::factory()->count(20)->make([
            'itemId' => $item->getKey(),
        ])->unique()->toArray();

        ItemAction::upsert($itemActions, ['itemId', 'actionName'], ['actionDescription', 'actionValue']);

        $itemActionsFromDatabase = ItemAction::where('itemId', 1)->get()->toArray();

        $this->assertEqualsCanonicalizing($itemActions, $itemActionsFromDatabase);
    }
}
