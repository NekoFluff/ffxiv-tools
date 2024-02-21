<?php

namespace App\Console\Commands;

use App\Http\Clients\Universalis\UniversalisClient;
use App\Http\Clients\XIV\XIVClient;
use App\Http\Controllers\GetRecipeController;
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
                    $recipe = $this->ffxivService->getRecipe($recipeObj["ID"]);
                }

                if ($recipe) {
                    $mbListings = $this->ffxivService->getMarketBoardListings($server, $recipe->itemIDs());
                    $this->ffxivService->updateRecipeCosts($recipe, $mbListings);
                    $this->ffxivService->getMarketBoardSales($server, $recipe->item->id);
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
