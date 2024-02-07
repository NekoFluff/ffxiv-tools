<?php

use App\Http\Controllers\UniversalisController;
use App\Http\Controllers\XIVController;
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
        return inertia(
            'Recipes',
            [
                "recipe" => $xivController->searchRecipe($itemID),
                "history" => $universalisController->getMarketBoardHistory("Goblin", $itemID) ?? [],
                "listings" => $universalisController->getMarketBoardData("Goblin", [$itemID])["listings"] ?? [],
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
