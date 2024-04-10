<?php

namespace App\Console\Commands;

use App\Models\Listing;
use App\Models\Recipe;
use App\Services\FFXIVService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecipesDaemon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recipes:daemon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refreshes the market board current listings and sale history for recipes';

    protected FFXIVService $ffxivService;

    protected int $totalCount = 0;

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
            $this->refreshOldRecipes($server);
            sleep(60 * 5);
        }
    }

    private function refreshOldRecipes(string $server): void
    {
        $count = 0;

        /** @var Collection<int, Recipe> $recipes */
        $recipes = Recipe::with('item')
            ->leftJoin('market_prices', function ($join) use ($server) {
                $join->on('recipes.item_id', '=', 'market_prices.item_id')
                     ->where('market_prices.server', '=', $server);
            })
            ->select('recipes.*')
            ->where('market_prices.updated_at', '<', now()->subHours(24))
            ->groupBy('recipes.id')
            ->orderBy('market_prices.updated_at', 'asc')
            ->limit(100)
            ->get();
        $recipesCount = $recipes->count();

        foreach ($recipes as $recipe) {
            $count += 1;
            $this->totalCount += 1;
            Log::info('['.$count.'/'.$recipesCount.']'.' Processing recipe '.$recipe->item->name.' ('.$recipe->id.') | Item ID: '.$recipe->item_id);
            $this->ffxivService->refreshMarketboardListings($server, $recipe->itemIDs());
            DB::transaction(function () use ($recipe, $server) {
                $listings = Listing::whereIn('item_id', $recipe->itemIDs())->get()->groupBy('item_id');
                $this->ffxivService->updateMarketPrices($server, $recipe, $listings);
                $this->ffxivService->updateRecipeCosts($server, $recipe);
                $this->ffxivService->refreshMarketBoardSales($server, $recipe->item_id);
            });

            echo '['.now()->toDateTimeString().'] #'.$this->totalCount.' ['.$count.'/'.$recipesCount.'] Processing recipe '.$recipe->item->name.' | Mem Usage: '.intval(memory_get_usage(true) / 1024)." KB \n";
            $profit = $recipe->item->marketPrice($server)?->price - $recipe->craftingCost($server)->optimal_craft_cost;
            echo 'Profit: '.$profit.' | Optimal Craft Cost: '.$recipe->craftingCost($server)->optimal_craft_cost."\n";
            sleep(2);
        }
    }
}
