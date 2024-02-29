<?php

namespace App\Console\Commands;

use App\Models\Recipe;
use App\Services\FFXIVService;
use Illuminate\Console\Command;
use Log;

class ForceRefreshRecentRecipes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recipes:forceRefreshRecentRecipes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Force refresh recipes that have been updated in the last 3 days.';

    protected FFXIVService $ffxivService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(FFXIVService $ffxivService)
    {
        parent::__construct();

        $this->ffxivService = $ffxivService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $recipes = Recipe::where('updated_at', '>', now()->subDays(3))->get();
        $totalCount = $recipes->count();
        Log::info("Refreshing {$totalCount} recipes");
        $recipes->each(
            function (Recipe $recipe, $idx) use ($totalCount) {
                Log::info("Refreshing recipe {$recipe->item_id} (".($idx + 1)."/{$totalCount})");
                $this->ffxivService->getRecipe($recipe->id, true);

                sleep(1);
            }
        );

    }
}
