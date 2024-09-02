<?php

namespace App\Console\Commands;

use App\Jobs\RefreshItem;
use App\Models\Enums\Server;
use App\Services\FFXIVService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MostRecentlyUpdatedRecipesDaemon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recipes:mru-daemon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refreshes the most recently updated items from universalis';

    protected FFXIVService $ffxivService;

    protected int $totalCount = 0;

    protected int $timestamp = 0;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(FFXIVService $ffxivService)
    {
        parent::__construct();

        $this->ffxivService = $ffxivService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        DB::disableQueryLog();
        ini_set('memory_limit', '256M');
        $server = Server::GOBLIN;

        /** @phpstan-ignore-next-line */
        while (true) {
            $this->refreshRecipes($server);
            sleep(10);
        }
    }

    private function refreshRecipes(Server $server): void
    {
        $count = 0;

        $items = $this->ffxivService->fetchMostRecentlyUpdatedItems($server);
        $items = collect($items)->filter(function ($item) {
            return $item['lastUploadTime'] > $this->timestamp;
        });

        if ($items->isEmpty()) {
            return;
        }

        $this->timestamp = $items->max('lastUploadTime');
        $itemsCount = $items->count();

        foreach ($items as $item) {
            $recipe = $this->ffxivService->getRecipeByItemID($item['itemID']);
            $count += 1;

            if ($recipe) {
                $this->totalCount += 1;
                Log::info('['.$count.'/'.$itemsCount.']'.' Dispatching job to process recipe '.$recipe->item->name.' ('.$recipe->id.') | Item ID: '.$recipe->item_id);

                RefreshItem::dispatch($recipe->item_id, $server);
            } else {
                Log::info('['.$count.'/'.$itemsCount.'] '.'Recipe not found for item ID '.$item['itemID']);
                echo '['.now()->toDateTimeString().'] ['.$count.'/'.$itemsCount.'] Recipe not found for item ID '.$item['itemID']."\n";
            }
            sleep(2);
        }

    }
}
