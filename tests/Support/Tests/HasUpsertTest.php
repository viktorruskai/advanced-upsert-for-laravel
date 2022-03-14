<?php
declare(strict_types=1);

namespace Tests;

use App\Models\Item;
use App\Models\ItemAction;
use PHPUnit\Framework\TestCase;

class HasUpsertTest extends TestCase
{

    public function testBasicUpsert(): void
    {
        $item = Item::factory()->create([
            'id' => 1,
        ]);

        $itemActions = ItemAction::factory()->count(20)->make([
            'itemId' => $item->getKey(),
        ])->unique()->toArray();

        ItemAction::upsert($itemActions, ['itemId', 'actionName'], ['actionDescription', 'actionValue']);

        $itemActionsFromDatabase = ItemAction::where('itemId', 1)->get()->toArray();

        $this->assertEqualsCanonicalizing($itemActions, $itemActionsFromDatabase);
    }
}