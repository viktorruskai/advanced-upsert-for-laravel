<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Item;
use App\Models\ItemAction;
use Illuminate\Console\Command;

class UpsertQueryTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'upsert:test';

    /**
     * The console command description.
     */
    protected $description = 'This command will try upsert data to the database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $item = Item::factory()->make();

        $itemActions = ItemAction::factory()->count(20)->make([
            'itemId' => $item->getKey(),
        ]);

        dump($itemActions);

        $this->output->block('I am here...');
        return 0;
    }
}
