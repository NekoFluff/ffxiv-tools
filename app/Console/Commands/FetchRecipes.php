<?php

namespace App\Console\Commands;

use App\Http\Clients\XIV\XIVClient;
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
    protected $signature = 'recipes:fetch {--after=1337}';

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
    public function handle(XIVClient $xivClient): void
    {
        // Telescope::tag(fn () => ['command:'.$this->signature, 'start:'.$this->startTime]);

        $after = intval($this->option('after'));
        $limit = 100;
        $server = Server::GOBLIN;
        do {
            error_log("Fetching recipes after ID ".$after);

            $xivRecipes = $xivClient->fetchRecipes($after, $limit);

            foreach ($xivRecipes as $xivRecipe) {
                // error_log('Processing recipe '.$xivRecipe->ResultItem->Name.' ('.$xivRecipe->ID.')');
                $recipe = Recipe::where('id', $xivRecipe->ID)->first();

                if ($recipe === null) {
                    $recipe = $this->ffxivService->getRecipe($xivRecipe->ID);
                    // error_log('Recipe not found in database, creating new one: '.$xivRecipe->ResultItem->Name);
                } else {
                    continue;
                    // error_log('Recipe found in database, updating: '.$xivRecipe->ResultItem->Name);
                }

                // error_log('Dispatching job to refresh item for recipe '.$recipe->id.' ('.$xivRecipe->ResultItem->Name.')');
                RefreshItem::dispatch($recipe->item_id, $server);

                // error_log('Sleeping for 5 seconds');
                sleep(5);
            }

            $after = $xivRecipes->last()?->ID ?? $after + $limit;
            sleep(5);
        } while (! empty($xivRecipes));
    }
}
