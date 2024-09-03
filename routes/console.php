<?php

use App\Console\Commands\RefreshMostRecentlyUpdatedItems;
use App\Console\Commands\RefreshOldItems;
use App\Console\Commands\RefreshProfitableRecipes;
use Illuminate\Support\Facades\Schedule;

Schedule::command(RefreshOldItems::class)->cron('*/15 * * * *')->runInBackground();

Schedule::command(RefreshMostRecentlyUpdatedItems::class)->cron('*/3 * * * *')->runInBackground();

Schedule::command(RefreshProfitableRecipes::class)->cron('0 9 * * *')->runInBackground();
