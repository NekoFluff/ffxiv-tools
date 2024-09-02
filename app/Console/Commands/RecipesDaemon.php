<?php

namespace App\Console\Commands;

use App\Jobs\RefreshItem;
use App\Models\Enums\Server;
use App\Models\Recipe;
use App\Services\FFXIVService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecipesDaemon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recipes:daemon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refreshes the market board current listings and sale history for recipes';

    protected FFXIVService $ffxivService;

    protected int $totalCount = 0;

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
    public function handle(): void
    {
        DB::disableQueryLog();
        ini_set('memory_limit', '256M');
        $server = Server::GOBLIN;

        /** @phpstan-ignore-next-line */
        while (true) {
            $this->refreshOldRecipes($server);
            sleep(60 * 5);
        }
    }

    private function refreshOldRecipes(Server $server): void
    {
        $count = 0;

        /** @var Collection<int, Recipe> $recipes */
        $recipes = Recipe::with('item')
            ->leftJoin('market_prices', function ($join) use ($server) {
                $join->on('recipes.item_id', '=', 'market_prices.item_id')
                    ->where('market_prices.server', '=', $server);
            })
            ->select('recipes.*')
            ->where('market_prices.updated_at', '<', now()->subHours(24))
            ->groupBy('recipes.id')
            ->orderByRaw('MIN(market_prices.updated_at) ASC')
            ->limit(100)
            ->get();
        $recipesCount = $recipes->count();

        foreach ($recipes as $recipe) {
            $count += 1;
            $this->totalCount += 1;
            Log::info('['.$count.'/'.$recipesCount.']'.' Dispatching job to proccess recipe '.$recipe->item->name.' ('.$recipe->id.') | Item ID: '.$recipe->item_id);

            RefreshItem::dispatch($recipe->item_id, $server);

            sleep(2);
        }
    }
}
