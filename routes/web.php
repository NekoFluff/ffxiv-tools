<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ItemRetainerController;
use App\Http\Controllers\ItemSearchController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RetainerController;
use App\Http\Controllers\ServerController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'welcome'])->name('welcome');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard');

Route::get('/items/{id}', [ItemController::class, 'show'])->where('id', '\d+')->name('item.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/retainers', [RetainerController::class, 'index'])->name('retainers');
    Route::get('/retainers/{retainer}/edit', [RetainerController::class, 'edit'])->where('retainer', '\d+')->name('retainer.edit');
    Route::post('/retainers', [RetainerController::class, 'store'])->name('retainer.store');
    Route::put('/retainers/{retainer}', [RetainerController::class, 'update'])->where('retainer', '\d+')->name('retainer.update');
    Route::delete('/retainers/{retainer}', [RetainerController::class, 'destroy'])->where('retainer', '\d+')->name('retainer.destroy');
    Route::post('/retainers/{retainer}/items', [ItemRetainerController::class, 'store'])->where('retainer', '\d+')->name('retainer.items.store');
    Route::delete('/retainers/{retainer}/items/{item}', [ItemRetainerController::class, 'destroy'])->where(['retainer' => '\d+', 'item' => '\d+'])->name('retainer.items.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// API helpers (session-based, web middleware)
Route::get('/api/items/search', [ItemSearchController::class, 'index'])->name('api.items.search');
Route::post('/api/server', [ServerController::class, 'update'])->name('api.server');

require __DIR__.'/auth.php';
