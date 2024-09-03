<?php

namespace App\Console\Commands;

use App\Jobs\RefreshItem;
use App\Models\Enums\Server;
use App\Models\MarketPrice;
use App\Models\Recipe;
use App\Services\FFXIVService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Laravel\Telescope\Telescope;

class RefreshProfitableRecipes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recipes:refresh-profitable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refreshes the market board current listings and sale history for the most profitable recipes';

    protected FFXIVService $ffxivService;

    protected int $startTime = 0;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(FFXIVService $ffxivService)
    {
        parent::__construct();

        $this->ffxivService = $ffxivService;

        $this->startTime = intval(now()->timestamp);
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Telescope::tag(fn () => ['command:'.$this->signature, 'start:'.$this->startTime]);

        $server = Server::GOBLIN;

        $recipes = Recipe::with('item')
            ->leftJoin('items', 'recipes.item_id', '=', 'items.id')
            ->leftJoin('sales', 'items.id', '=', 'sales.item_id')
            ->leftJoin('market_prices', 'items.id', '=', 'market_prices.item_id')
            ->select('recipes.*')
            ->where('market_prices.price', '>', 'recipes.optimal_craft_cost')
            ->where('market_prices.price', '!=', MarketPrice::DEFAULT_MARKET_PRICE)
            ->whereRaw('DATE(sales.timestamp) > (NOW() - INTERVAL 7 DAY)')
            ->groupBy('recipes.id')
            ->orderByRaw('(market_price - optimal_craft_cost) * SUM(sales.quantity) desc')
            ->limit(1000)->get();

        foreach ($recipes as $index => $recipe) {
            if ($recipe->updated_at->diffInMinutes() < 60) {
                continue;
            }

            Log::info('['.($index + 1).'/'.count($recipes).']'.' Dispatching job to process recipe '.$recipe->item->name.' ('.$recipe->id.') | Item ID: '.$recipe->item_id);

            RefreshItem::dispatch($recipe->item_id, $server);

            sleep(10);
        }
    }
}
