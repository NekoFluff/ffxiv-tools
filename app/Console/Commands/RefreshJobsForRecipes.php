<?php

namespace App\Console\Commands;

use App\Http\Clients\XIV\XIVClient;
use App\Models\Recipe;
use Illuminate\Console\Command;
use Laravel\Telescope\Telescope;

class RefreshJobsForRecipes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recipes:refresh-jobs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refreshes the class job data for all recipes';

    protected int $startTime;

    public function __construct()
    {
        parent::__construct();

        $this->startTime = intval(now()->timestamp);
    }

    /**
     * Execute the console command.
     */
    public function handle(XIVClient $xivClient): void
    {
        Telescope::tag(fn () => ['command:'.$this->signature, 'start:'.$this->startTime]);

        $recipes = Recipe::all();
        $recipes->each(
            function (Recipe $recipe) use ($xivClient) {
                $xivRecipe = cache()->remember('recipe_'.$recipe->id, now()->addMinutes(30), function () use ($xivClient, $recipe) {
                    logger("Fetching recipe {$recipe->id}");

                    return $xivClient->fetchRecipe($recipe->id);
                });

                if (! $xivRecipe) {
                    return;
                }

                $recipe->update([
                    'class_job' => $xivRecipe->ClassJobName,
                    'class_job_level' => $xivRecipe->ClassJobLevel,
                    // 'class_job_icon' => $xivRecipe->ClassJobIcon,
                ]);

                sleep(1);
            }
        ) ;

    }
}
