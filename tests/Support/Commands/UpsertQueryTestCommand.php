<?php
declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpsertQueryTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:upsert';

    /**
     * The console command description.
     */
    protected $description = 'This command will try upsert data to the database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->output->block('I am here...');
        return 0;
    }
}
