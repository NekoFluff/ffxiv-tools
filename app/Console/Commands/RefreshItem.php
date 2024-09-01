<?php

namespace App\Console\Commands;

use App\Jobs\RefreshItem as JobsRefreshItem;
use Illuminate\Console\Command;

class RefreshItem extends Command
{
    protected $signature = 'app:refresh-item {itemID : The ID of the item to refresh.}';

    protected $description = 'Dispatch a job to refresh an item in the database.';

    public function handle(): void
    {
        $itemID = intval($this->argument('itemID'));

        JobsRefreshItem::dispatch($itemID);
    }
}
