<?php

namespace App\Console\Commands;

use App\Jobs\RefreshItem as JobsRefreshItem;
use Illuminate\Console\Command;
use Laravel\Telescope\Telescope;

class RefreshItem extends Command
{
    protected $signature = 'app:refresh-item {itemID : The ID of the item to refresh.}';

    protected $description = 'Dispatch a job to refresh an item in the database.';

    public function handle(): void
    {
        Telescope::tag(fn () => ['command:app:refresh-item', 'start:'.now()->timestamp]);

        $itemID = intval($this->argument('itemID'));

        JobsRefreshItem::dispatch($itemID);
    }
}
