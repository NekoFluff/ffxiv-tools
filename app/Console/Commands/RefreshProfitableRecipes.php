<?php

namespace App\Console\Commands;

use App\Http\Clients\Universalis\UniversalisClient;
use App\Http\Clients\XIV\XIVClient;
use App\Http\Controllers\GetRecipeController;
use App\Models\Item;
use App\Models\Recipe;
use App\Services\FFXIVService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RefreshProfitableRecipes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recipes:refreshProfitable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refreshes profitable recipes';

    protected FFXIVService $ffxivService;

    /**
     * Create a new command instance.
     *
     * @param  FFXIVService  $ffxivService
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
    public function handle()
    {
        $server = "Goblin";

        $recipes = Recipe::with('item')
            ->join('items', 'recipes.item_id', '=', 'items.id')
            ->join('sales', 'items.id', '=', 'sales.item_id')
            ->select('recipes.*')
            ->where('items.market_price', '>', 'recipes.optimal_craft_cost')
            ->where('items.market_price', '!=', Item::DEFAULT_MARKET_PRICE)
            ->whereRaw('DATE(sales.timestamp) > (NOW() - INTERVAL 7 DAY)')
            ->groupBy('recipes.id')
            ->orderByRaw('(market_price - optimal_craft_cost) * SUM(sales.quantity) desc')
            ->limit(3000)->get();

        foreach ($recipes as $recipe) {
            Log::info("Processing recipe " . $recipe->item->name . " (" . $recipe->id . ") | Item ID: " . $recipe->item_id);
            $mbListings = $this->ffxivService->getMarketBoardListings($server, $recipe->itemIDs());
            $this->ffxivService->updateMarketPrices($recipe, $mbListings);
            $this->ffxivService->updateRecipeCosts($recipe);
            $this->ffxivService->getMarketBoardSales($server, $recipe->item_id);
            sleep(1);
        }
    }

}
