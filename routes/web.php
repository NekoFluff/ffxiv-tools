<?php

use App\Http\Controllers\GetRecipeController;
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

Route::get('/{itemID}', GetRecipeController::class)->where('name', '.*');
