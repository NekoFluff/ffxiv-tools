<?php

namespace App\Console\Commands;

use App\Http\Controllers\UniversalisController;
use App\Models\Item;
use App\Models\Recipe;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
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

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $universalisController = new UniversalisController();
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
            $mb_data = $universalisController->getMarketBoardListings($server, $recipe->itemIDs());
            $recipe->populateCosts($mb_data);
            $universalisController->getMarketBoardHistory($server, $recipe->item_id);
            sleep(1);
        }
    }

}
