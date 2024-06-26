<?php

use App\Http\Controllers\GetRecipeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });

Route::get('/', function () {
    return Inertia::render('Dashboard', [
        'recipe' => null,
        'history' => [],
        'listings' => [],
        'item' => null,
        'lastUpdated' => '',
    ]);
})->middleware([])->name('dashboard');

Route::get('/items/{itemID}', GetRecipeController::class)->where('itemID', '\d*')->name('recipe.get');

Route::get('/retainers', function () {
    return Inertia::render('Retainers', []);
})->middleware([])->name('retainers');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
require __DIR__.'/api.php';
