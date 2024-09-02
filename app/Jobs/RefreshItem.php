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
use Illuminate\Support\Facades\Log;

class RefreshItem implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public int $itemID, public Server $server = Server::GOBLIN)
    {
    }

    public function handle(FFXIVService $service): void
    {
        Log::withContext(['itemID' => $this->itemID, 'server' => $this->server]);

        DB::transaction(function () use ($service) {
            $recipe = $service->getRecipeByItemID($this->itemID);

            $item = $recipe->item ?? Item::find($this->itemID);
            if ($recipe) {
                $service->refreshMarketboardListings($this->server, $recipe->itemIDs());
                $listings = Listing::whereIn('item_id', $recipe->itemIDs())->get()->groupBy('item_id');
                $service->updateMarketPrices($this->server, $recipe, $listings);
                $service->updateRecipeCosts($this->server, $recipe);
                $service->refreshMarketBoardSales($this->server, $recipe->item_id);
            } elseif ($item) {
                $service->refreshMarketboardListings($this->server, [$item->id]);
                $listings = Listing::where('item_id', $item->id)->get();
                if (! $listings->isEmpty()) {
                    $service->updateMarketPrice($this->server, $item, $listings);
                }
                $service->refreshMarketBoardSales($this->server, $item->id);
            }
        });
    }

    public function uniqueId(): string
    {
        return 'refresh-item-'.$this->itemID;
    }
}
