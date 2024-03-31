<?php

use App\Http\Controllers\ItemRetainerController;
use App\Http\Controllers\RetainersController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->middleware('auth')->group(function () {
    Route::get('retainers', [RetainersController::class, 'index'])->name('retainers.list');
    Route::post('retainers', [RetainersController::class, 'store'])->name('retainers.store');
    Route::delete('retainers/{retainerID}', [RetainersController::class, 'destroy'])->name('retainers.destroy');

    Route::post('retainers/{retainerID}/items', [ItemRetainerController::class, 'store'])->name('retainers.items.store');
    Route::delete('retainers/{retainerID}/items', [ItemRetainerController::class, 'destroy'])->name('retainers.items.destroy');
});
