<?php

use App\Http\Controllers\UniversalisController;
use App\Http\Controllers\XIVController;
use App\Models\Listing;
use App\Models\Recipe;
use App\Models\Sale;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     sleep(2);
//     return inertia(
//         'Home',
//         [
//             "username" => "John Doe",
//         ]
//     );
// });

// Route::get(
//     '/about',
//     function () {
//         return inertia('About');
//     }
// );

Route::get('/', function () {
    return inertia(
        'Recipes',
        [
            "recipe" => null,
        ]
    );
});

Route::get('/{itemID}', function ($itemID) {
    $universalisController = new UniversalisController();
    $xivController = new XIVController();

    if ($itemID) {
        // Recipe
        $recipe = Recipe::with('ingredients')->where('item_id', $itemID)->first();
        if ($recipe) {
            if ($recipe->updated_at->diffInMinutes(now()) > 15) {
                $xivController->reloadRecipeData($recipe); // Updates listings
            }
            $recipe->alignAmounts(1);
        } else {
            $recipe = $xivController->searchRecipe($itemID);
        }

        // Sales
        $sales = Sale::where('item_id', $itemID)->where('timestamp', '>=', Carbon::now()->subDays(7))->latest()->get();
        if ($sales->isEmpty() || $recipe->updated_at->diffInMinutes(now()) > 60) {
            $sales = $universalisController->getMarketBoardHistory("Goblin", $itemID);
        } else {
            $sales = $universalisController->translateToHistory($sales);
        }

        // Listings
        $listings = Listing::where('item_id', $itemID)->orderBy('price_per_unit', 'asc')->get();

        return inertia(
            'Recipes',
            [
                "recipe" => $recipe,
                "history" => $sales ?? [],
                "listings" => $listings ?? [],
            ]
        );
    }

    return inertia(
        'Recipes',
        [
            "recipe" => [],
            "history" => [],
            "listings" => [],
        ]
    );
})->where('name', '.*');
