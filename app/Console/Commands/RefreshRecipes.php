<?php

namespace App\Console\Commands;

use App\Models\Recipe;
use App\Services\FFXIVService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RefreshRecipes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recipes:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refreshes all recipes';

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
    public function handle()
    {
        $page = 1;
        $recipesStr = '';
        $server = 'Goblin';
        do {
            Log::info('Fetching recipes page '.$page);

            $recipesStr = file_get_contents('https://xivapi.com/recipe?page='.$page);

            $recipeJsonObjs = json_decode($recipesStr, true)['Results'] ?? [];
            foreach ($recipeJsonObjs as $recipeObj) {
                if (empty($recipeObj['ID'])) {
                    continue;
                }

                Log::info('Processing recipe '.$recipeObj['Name'].' ('.$recipeObj['ID'].')');
                $recipe = Recipe::where('id', $recipeObj['ID'])->first();

                if ($recipe === null) {
                    $recipe = $this->ffxivService->getRecipe($recipeObj['ID']);
                }

                if ($recipe) {
                    $mbListings = $this->ffxivService->getMarketBoardListings($server, $recipe->itemIDs());
                    $this->ffxivService->updateMarketPrices($recipe, $mbListings);
                    $this->ffxivService->updateRecipeCosts($recipe);
                    $this->ffxivService->getMarketBoardSales($server, $recipe->item_id);
                } else {
                    Log::error('Failed to retrieve recipe ID '.$recipeObj['ID']);
                }

                Log::info('Sleeping for 3 seconds');
                sleep(3);
            }

            $page += 1;
            sleep(5);
        } while (! empty($recipeJsonObjs));
    }
}
