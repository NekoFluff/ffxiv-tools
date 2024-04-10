<?php

namespace App\Console\Commands;

use App\Models\Listing;
use App\Models\Recipe;
use App\Services\FFXIVService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
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
        $server = 'Goblin';

        /** @phpstan-ignore-next-line */
        while (true) {
            $this->refreshRecipes($server);
            sleep(10);
        }
    }

    private function refreshRecipes(string $server): void
    {
        $count = 0;

        /** @var Collection<int, Recipe> $recipes */
        $items = $this->ffxivService->fetchMostRecentlyUpdatedItems($server);
        $items = collect($items)->filter(function ($item) {
            return $item['lastUploadTime'] > $this->timestamp;
        });
        $recipes = Recipe::whereIn('item_id', $items->pluck('itemID'))->get();
        $recipesCount = $recipes->count();

        foreach ($recipes as $recipe) {
            $count += 1;
            $this->totalCount += 1;
            Log::info('['.$count.'/'.$recipesCount.']'.' Processing recipe '.$recipe->item->name.' ('.$recipe->id.') | Item ID: '.$recipe->item_id);
            $this->ffxivService->refreshMarketboardListings($server, $recipe->itemIDs());
            DB::transaction(function () use ($recipe, $server) {
                $listings = Listing::whereIn('item_id', $recipe->itemIDs())->get()->groupBy('item_id');
                $this->ffxivService->updateMarketPrices($server, $recipe, $listings);
                $marketPrice = $recipe->item->marketPrice($server);
                if ($marketPrice !== null) {
                    $marketPrice->updated_at = now();
                    $marketPrice->save();
                }

                $this->ffxivService->updateRecipeCosts($server, $recipe);
                $this->ffxivService->refreshMarketBoardSales($server, $recipe->item_id);
            });

            echo '['.now()->toDateTimeString().'] #'.$this->totalCount.' ['.$count.'/'.$recipesCount.'] Processing recipe '.$recipe->item->name.' | Mem Usage: '.intval(memory_get_usage(true) / 1024)." KB \n";
            $profit = $recipe->item->marketPrice($server)?->price - $recipe->craftingCost($server)->optimal_craft_cost;
            echo 'Profit: '.$profit.' | Optimal Craft Cost: '.$recipe->craftingCost($server)->optimal_craft_cost."\n";
            sleep(2);
        }

        $this->timestamp = now()->timestamp * 1000;
    }
}
