<?php

namespace App\Console\Commands;

use App\Models\Item;
use App\Models\Listing;
use App\Models\Recipe;
use App\Services\FFXIVService;
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
    protected $description = 'Refreshes the market board current listings and sale history for the most profitable recipes';

    protected FFXIVService $ffxivService;

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
        $server = 'Goblin';

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

        foreach ($recipes as $index => $recipe) {
            if ($recipe->updated_at->diffInMinutes() < 60) {
                continue;
            }

            Log::info('['.($index + 1).'/'.count($recipes).']'.' Processing recipe '.$recipe->item->name.' ('.$recipe->id.') | Item ID: '.$recipe->item_id);
            $this->ffxivService->refreshMarketboardListings($server, $recipe->itemIDs());
            DB::transaction(function () use ($recipe, $server) {
                $listings = Listing::whereIn('item_id', $recipe->itemIDs())->get()->groupBy('item_id');
                $this->ffxivService->updateMarketPrices($recipe, $listings);
                $this->ffxivService->updateRecipeCosts($recipe);
                $this->ffxivService->refreshMarketBoardSales($server, $recipe->item_id);
            });
            sleep(1);
        }
    }
}
