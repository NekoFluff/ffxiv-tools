<?php

namespace App\Console\Commands;

use App\Http\Controllers\UniversalisController;
use App\Http\Controllers\XIVController;
use App\Models\Recipe;
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

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $xivController = new XIVController();
        $universalisController = new UniversalisController();
        $page = 85;
        $recipesJson = ""; // pages 1  - 33 (inclusive) need to be refreshed
        $server = "Goblin";
        do {
            Log::info("Fetching recipes page " . $page);

            $recipesJson = file_get_contents("https://xivapi.com/recipe?page=" . $page);

            $recipesObjs = json_decode($recipesJson, true)["Results"] ?? [];
            foreach ($recipesObjs as $recipeObj) {
                if (empty($recipeObj["ID"])) {
                    continue;
                }

                Log::info("Processing recipe " . $recipeObj["Name"] . " (" . $recipeObj["ID"] . ")");
                $recipe = Recipe::find($recipeObj["ID"]);

                if ($recipe !== null) {
                    Log::info("Recipe already exists, skipping");
                    // Update history if necessary
                    if ($recipe->item->sales->isEmpty()) {
                        Log::info("Fetching market board history for " . $recipe->item->name . " (" . $recipe->item->id . ")");
                        $universalisController->getMarketBoardHistory($server, $recipe->item->id);
                    }
                    sleep(1);
                    continue;
                }

                $recipe = $xivController->getRecipe($recipeObj["ID"]);
                if ($recipe) {
                    $xivController->reloadRecipeListings($recipe);
                    Log::info("Recipe #" . $recipe->id . " for " . $recipe->item->name . " (" . $recipe->item->id . ") created");

                    // Update history if necessary
                    if ($recipe->item->sales->isEmpty()) {
                        Log::info("Fetching market board history for " . $recipe->item->name . " (" . $recipe->item->id . ")");
                        $universalisController->getMarketBoardHistory($server, $recipe->item->id);
                    }
                } else {
                    Log::error("Failed to retrieve recipe, skipping");
                }

                Log::info("Sleeping for 5 seconds");
                sleep(5);
            }

            $page += 1;
            sleep(10);

            if ($page > 120) {
                break;
            }
        } while (!empty($recipesObjs));
    }

}
