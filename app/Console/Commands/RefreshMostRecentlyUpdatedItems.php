<?php

namespace App\Console\Commands;

use App\Jobs\RefreshItem;
use App\Models\Enums\Server;
use App\Services\FFXIVService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Telescope\Telescope;

class RefreshMostRecentlyUpdatedItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recipes:refresh-most-recently-updated-items';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refreshes the most recently updated items (last minute) from universalis';

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

        $this->timestamp = now()->subMinutes(1)->subSeconds(5)->getTimestampMs();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $startTime = now()->getTimestamp();
        Telescope::tag(fn () => ['command:'.$this->signature, 'start:'.$startTime]);

        DB::disableQueryLog();
        ini_set('memory_limit', '256M');
        $server = Server::GOBLIN;

        $this->refresh($server);
    }

    private function refresh(Server $server): void
    {
        $count = 0;

        $items = $this->ffxivService->fetchMostRecentlyUpdatedItems($server);
        $items = collect($items)->filter(function ($item) {
            return $item['lastUploadTime'] >= $this->timestamp;
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
                if ($recipe->item->marketPrice($server)?->updated_at >= now()->subMinutes(3)) {
                    Log::info('['.$count.'/'.$itemsCount.']'.' Skipping. Recipe for item '.$recipe->item->name.' ('.$recipe->item->id.') has been updated in the last 3 minutes.', [
                        'itemID' => $recipe->item->id,
                    ]);

                    continue;
                }

                $this->totalCount += 1;
                Log::info('['.$count.'/'.$itemsCount.']'.' Dispatching job to process item '.$recipe->item->name.' ('.$recipe->item->id.')', [
                    'itemID' => $recipe->item->id,
                ]);

                RefreshItem::dispatch($recipe->item_id, $server);
            } else {
                Log::info('['.$count.'/'.$itemsCount.'] '.'Recipe not found for item ID '.$item['itemID'], [
                    'itemID' => $item['itemID'],
                ]);
            }
            sleep(1);
        }
    }
}
