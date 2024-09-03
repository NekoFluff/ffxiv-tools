<?php

namespace App\Console\Commands;

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
    public function handle(): void
    {
        Telescope::tag(fn () => ['command:'.$this->signature, 'start:'.$this->startTime]);

        $recipes = Recipe::all();
        $recipes->each(
            function (Recipe $recipe) {
                $id = $recipe->id;
                $recipeData = cache()->remember('recipe_'.$id, now()->addMinutes(30), function () use ($id) {
                    logger("Fetching recipe {$id}");

                    return file_get_contents("https://xivapi.com/recipe/{$id}");
                });

                if ($recipeData === false) {
                    return;
                }

                $recipeData = json_decode($recipeData, true);

                $recipe->update([
                    'class_job' => $recipeData['ClassJob']['NameEnglish'],
                    'class_job_level' => $recipeData['RecipeLevelTable']['ClassJobLevel'],
                    'class_job_icon' => $recipeData['ClassJob']['Icon'],
                ]);

                sleep(1);
            }
        );

    }
}
