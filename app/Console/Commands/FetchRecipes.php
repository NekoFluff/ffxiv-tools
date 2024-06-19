<?php

namespace App\Console\Commands;

use App\Models\Enums\Server;
use App\Models\Listing;
use App\Models\Recipe;
use App\Services\FFXIVService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FetchRecipes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recipes:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetches all recipes from XIVAPI and populates the database with them.';

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
        $page = 1;
        $recipesStr = '';
        $server = Server::GOBLIN;
        do {
            Log::info('Fetching recipes page '.$page);

            $recipesStr = file_get_contents('https://xivapi.com/recipe?page='.$page) ?: '';

            $recipeJsonObjs = json_decode($recipesStr, true)['Results'] ?? [];
            foreach ($recipeJsonObjs as $recipeObj) {
                if (empty($recipeObj['ID'])) {
                    continue;
                }

                Log::info('Processing recipe '.$recipeObj['Name'].' ('.$recipeObj['ID'].')');
                $recipe = Recipe::where('id', $recipeObj['ID'])->first();

                if ($recipe === null) {
                    $recipe = $this->ffxivService->getRecipe($recipeObj['ID']);
                } else {
                    continue;
                }

                if ($recipe) {
                    $this->ffxivService->refreshMarketboardListings($server, $recipe->itemIDs());
                    DB::transaction(function () use ($recipe, $server) {
                        $listings = Listing::whereIn('item_id', $recipe->itemIDs())->get()->groupBy('item_id');
                        $this->ffxivService->updateMarketPrices($server, $recipe, $listings);
                        $this->ffxivService->updateRecipeCosts($server, $recipe);
                        $this->ffxivService->refreshMarketBoardSales($server, $recipe->item_id);
                    });
                } else {
                    Log::error('Failed to retrieve recipe ID '.$recipeObj['ID']);
                }

                Log::info('Sleeping for 3 seconds');
                sleep(2);
            }

            $page += 1;
            sleep(5);
        } while (! empty($recipeJsonObjs));
    }
}
