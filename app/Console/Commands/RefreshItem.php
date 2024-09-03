<?php

namespace App\Console\Commands;

use App\Jobs\RefreshItem as JobsRefreshItem;
use Illuminate\Console\Command;
use Laravel\Telescope\Telescope;

class RefreshItem extends Command
{
    protected $signature = 'app:refresh-item {itemID : The ID of the item to refresh.}';

    protected $description = 'Dispatch a job to refresh an item in the database.';

    protected int $startTime;

    public function __construct()
    {
        parent::__construct();

        $this->startTime = intval(now()->timestamp);
    }

    public function handle(): void
    {
        Telescope::tag(fn () => ['command:'.$this->signature, 'start:'.$this->startTime]);

        $itemID = intval($this->argument('itemID'));

        JobsRefreshItem::dispatch($itemID);
    }
}
