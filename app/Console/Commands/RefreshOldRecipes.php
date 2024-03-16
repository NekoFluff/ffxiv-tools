<?php

namespace App\Console\Commands;

use App\Models\Listing;
use App\Models\Recipe;
use App\Services\FFXIVService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RefreshOldRecipes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recipes:refreshOld';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refreshes the market board current listings and sale history for recipes that have not been updated in the last 3 days.';

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
        DB::disableQueryLog();
        ini_set('memory_limit', '256M');
        $server = 'Goblin';

        $recipesCount = Recipe::with('item')
            ->join('items', 'recipes.item_id', '=', 'items.id')
            ->join('sales', 'items.id', '=', 'sales.item_id')
            ->select('recipes.*')
            ->where('recipes.updated_at', '<', now()->subDays(3))
            ->groupBy('recipes.id')
            ->orderBy('recipes.updated_at', 'asc')
            ->get()
            ->count();
        $count = 0;

        do {
            $recipes = Recipe::with('item')
                ->join('items', 'recipes.item_id', '=', 'items.id')
                ->join('sales', 'items.id', '=', 'sales.item_id')
                ->select('recipes.*')
                ->where('recipes.updated_at', '<', now()->subDays(3))
                ->groupBy('recipes.id')
                ->orderBy('recipes.updated_at', 'asc')
                ->limit(2000)
                ->get();

            foreach ($recipes as $recipe) {
                $count += 1;
                Log::info('['.$count.'/'.$recipesCount.']'.' Processing recipe '.$recipe->item->name.' ('.$recipe->id.') | Item ID: '.$recipe->item_id);
                $this->ffxivService->refreshMarketboardListings($server, $recipe->itemIDs());
                DB::transaction(function () use ($recipe, $server) {
                    $listings = Listing::whereIn('item_id', $recipe->itemIDs())->get()->groupBy('item_id');
                    $this->ffxivService->updateMarketPrices($recipe, $listings);
                    $this->ffxivService->updateRecipeCosts($recipe);
                    $this->ffxivService->refreshMarketBoardSales($server, $recipe->item_id);
                });
                echo '['.$count.'/'.$recipesCount.'] Mem Usage: '.intval(memory_get_usage(true) / 1024)." KB \n";
                sleep(1);
            }

            break; // Remove this line to process all recipes
        } while ($recipes->count() > 0);

        echo 'Done';
    }
}
