<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/items/{id}', App\Livewire\ItemDashboard::class)->where('id', '\d*')->name('item.show');

Route::get('/retainers', App\Livewire\RetainersDashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('retainers');
Route::get('/retainers/{retainer}/edit', App\Livewire\EditRetainer::class)->where('retainer', '\d*')->name('retainer.edit');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
