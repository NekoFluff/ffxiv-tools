<?php

namespace App\Console\Commands;

use App\Jobs\RefreshItem;
use App\Models\Enums\Server;
use App\Models\Recipe;
use App\Services\FFXIVService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Laravel\Telescope\Telescope;

class FetchRecipes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recipes:fetch {--page=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetches all recipes from XIVAPI and populates the database with them.';

    protected FFXIVService $ffxivService;

    protected int $startTime;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(FFXIVService $ffxivService)
    {
        parent::__construct();

        $this->ffxivService = $ffxivService;

        $this->startTime = intval(now()->timestamp);
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Telescope::tag(fn () => ['command:'.$this->signature, 'start:'.$this->startTime]);

        $page = intval($this->option('page'));
        $recipesStr = '';
        $server = Server::GOBLIN;
        do {
            Log::info('Fetching recipes page '.$page);
            error_log("Fetching recipes page ".$page);

            $recipesStr = file_get_contents('https://xivapi.com/recipe?page='.$page) ?: '';

            $recipeJsonObjs = json_decode($recipesStr, true)['Results'] ?? [];
            foreach ($recipeJsonObjs as $recipeObj) {
                if (empty($recipeObj['ID'])) {
                    continue;
                }

                Log::info('Processing recipe '.$recipeObj['Name'].' ('.$recipeObj['ID'].')');
                $recipe = Recipe::where('id', $recipeObj['ID'])->first();

                if ($recipe === null) {
                    $recipe = $this->ffxivService->getRecipe($recipeObj['ID']);
                } else {
                    continue;
                }

                RefreshItem::dispatch($recipe->item_id, $server);

                Log::info('Sleeping for 3 seconds');
                sleep(5);
            }

            $page += 1;
            sleep(5);
        } while (! empty($recipeJsonObjs));
    }
}
