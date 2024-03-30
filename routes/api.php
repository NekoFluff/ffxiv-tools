<?php

use App\Http\Controllers\RetainersController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->middleware('auth')->group(function () {
    Route::get('retainers', [RetainersController::class, 'index'])->name('retainers.list');
});
