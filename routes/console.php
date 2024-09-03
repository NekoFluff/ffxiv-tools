<?php

use App\Console\Commands\RefreshMostRecentlyUpdatedItems;
use App\Console\Commands\RefreshOldItems;
use App\Console\Commands\RefreshProfitableRecipes;
use Illuminate\Support\Facades\Schedule;

Schedule::command(RefreshOldItems::class)->cron('*/15 * * * *');

Schedule::command(RefreshMostRecentlyUpdatedItems::class)->cron('*/3 * * * *');

Schedule::command(RefreshProfitableRecipes::class)->cron('0 9 * * *');
