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
        $page = 1;
        $recipesJson = "";
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

                if ($recipe === null) {
                    $recipe = $xivController->getRecipe($recipeObj["ID"]);
                }

                if ($recipe) {
                    $mb_data = $universalisController->getMarketBoardListings($server, $recipe->itemIDs());
                    $recipe->populateCosts($mb_data);
                    $universalisController->getMarketBoardHistory($server, $recipe->item->id);
                } else {
                    Log::error("Failed to retrieve recipe ID " . $recipeObj["ID"]);
                }

                Log::info("Sleeping for 3 seconds");
                sleep(3);
            }

            $page += 1;
            sleep(5);
        } while (!empty($recipesObjs));
    }

}
