<?php

namespace App\Jobs;

use App\Models\Enums\Server;
use App\Models\Item;
use App\Models\Listing;
use App\Services\FFXIVService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class RefreshItem implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $itemID, public Server $server = Server::GOBLIN)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(FFXIVService $service): void
    {
        $recipe = $service->getRecipeByItemID($this->itemID);

        $item = $recipe->item ?? Item::find($this->itemID);
        if ($recipe) {
            $service->refreshMarketboardListings($this->server, $recipe->itemIDs());
            DB::transaction(function () use ($recipe, $service) {
                $listings = Listing::whereIn('item_id', $recipe->itemIDs())->get()->groupBy('item_id');
                $service->updateMarketPrices($this->server, $recipe, $listings);
                $service->updateRecipeCosts($this->server, $recipe);
                $service->refreshMarketBoardSales($this->server, $recipe->item_id);
            });
        } elseif ($item) {
            $service->refreshMarketboardListings($this->server, [$item->id]);
            DB::transaction(function () use ($item, $service) {
                $listings = Listing::where('item_id', $item->id)->get();
                if (! $listings->isEmpty()) {
                    $service->updateMarketPrice($this->server, $item, $listings);
                }
                $service->refreshMarketBoardSales($this->server, $item->id);
            });
        }

        // if ($recipe) {
        //     $recipe->alignAmounts($this->server, 1);
        // }

        // $item = $recipe?->item ?? $item;

        // $item?->fresh();
        // $lastUpdated = $item?->marketPrice($this->server)?->updated_at?->diffForHumans() ?? '';

        // return inertia(
        //     'Dashboard',
        //     [
        //         'canLogin' => Route::has('login'),
        //         'canRegister' => Route::has('register'),
        //         'retainers' => Retainer::where('user_id', auth()->id())->where('server', $this->server)->whereRelation('items', 'id', $recipe?->item->id)->get(),
        //         'recipe' => $recipe,
        //         'item' => $item,
        //         'history' => $aggregatedSales,
        //         'listings' => $listings,
        //         'lastUpdated' => $lastUpdated,
        //     ]
        // );
        // return CraftableItem::fromRecipe($recipe);
    }

    /**
     * Calculate a unique ID for the job.
     */
    public function uniqueId(): string
    {
        return 'refresh-item-'.$this->itemID;
    }
}
