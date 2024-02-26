<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Listing;
use App\Models\Sale;
use App\Services\FFXIVService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class GetRecipeController extends Controller
{
    private FFXIVService $service;

    public function __construct(FFXIVService $service)
    {
        $this->service = $service;
    }

    public function __invoke(int $itemID)
    {
        $recalculate = boolval(request()->query('recalculate', 0));
        $server = "Goblin";
        $itemID = intval($itemID);

        $recipe = $this->service->getRecipeByItemID($itemID);
        if ($recipe) {
            if ($recalculate || $recipe->updated_at?->diffInMinutes(now()) > 15) {
                DB::transaction(function () use ($recipe, $server) {
                    $mbListings = $this->service->getMarketBoardListings($server, $recipe->itemIDs());
                    $this->service->updateMarketPrices($recipe, $mbListings);
                    $this->service->updateRecipeCosts($recipe);
                    $this->service->getMarketBoardSales($server, $recipe->item_id);
                });
            }
        } else {
            $item = Item::find($itemID);
            if ($item && $item->updated_at?->diffInMinutes(now()) > 15) {
                DB::transaction(function () use ($item, $server) {
                    $mbListings = $this->service->getMarketBoardListings($server, [$item->id]);
                    $listings = $mbListings[$item->id] ?? collect([]);
                    if (!$listings->isEmpty()) {
                        $this->service->updateMarketPrice($item, $listings);
                    }
                    $this->service->getMarketBoardSales($server, $item->id);
                });
            }
        }

        // Sales
        $sales = Sale::where('item_id', $itemID)->where('timestamp', '>=', Carbon::now()->subDays(7))->latest()->get();
        $aggregatedSales = $this->service->aggregateSales($sales);

        // Listings
        $listings = Listing::where('item_id', $itemID)->orderBy('price_per_unit', 'asc')->get();

        if ($recipe) {
            $recipe->alignAmounts(1);
        }

        $lastUpdated = $recipe?->updated_at?->diffForHumans() ?? $item?->updated_at?->diffForHumans() ?? null;

        return inertia(
            'Recipe',
            [
                "recipe" => $recipe,
                "item" => $recipe?->item ?? $item ?? null,
                "history" => $aggregatedSales,
                "listings" => $listings,
                "lastUpdated" => $lastUpdated,
            ]
        );
    }
}
