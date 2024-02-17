<?php

namespace App\Console\Commands;

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
        $page = 3;
        $recipesJson = "";
        do {
            Log::info("Fetching recipes page " . $page);

            $recipesJson = file_get_contents("https://xivapi.com/recipe?page=" . $page);

            $recipesObjs = json_decode($recipesJson, true)["Results"] ?? [];
            foreach ($recipesObjs as $recipeObj) {
                Log::info("Processing recipe " . $recipeObj["Name"] . " (" . $recipeObj["ID"] . ")");
                $recipe = Recipe::find($recipeObj["ID"]);

                if ($recipe !== null) {
                    Log::info("Recipe already exists, skipping");
                    continue;
                }

                $recipe = $xivController->getRecipe($recipeObj["ID"]);
                if ($recipe) {
                    $xivController->reloadRecipeData($recipe);
                    Log::info("Recipe #" . $recipe->id . " for " . $recipe->item->name . " (" . $recipe->item->id . ") created");
                } else {
                    Log::error("Failed to retrieve recipe, skipping");
                }
                Log::info("Sleeping for 10 seconds");
                sleep(10);
            }

            $page += 1;
            sleep(10);

            if ($page > 120) {
                break;
            }
        } while (!empty($recipesObjs));
    }

}
