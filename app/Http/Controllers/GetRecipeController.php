<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Listing;
use App\Models\Sale;
use App\Services\FFXIVService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GetRecipeController extends Controller
{
    private FFXIVService $service;

    public function __construct(FFXIVService $service)
    {
        $this->service = $service;
    }

    public function __invoke(int $itemID)
    {
        $server = "Goblin";

        $item = Item::find($itemID);
        if (empty($item)) {
            return inertia(
                'Recipes',
                [
                    "recipe" => null,
                    "item" => null,
                    "history" => [],
                    "listings" => [],
                ]
            );
        }

        $recipe = $this->service->getRecipeByItemID($item->id);
        if ($recipe) {
            if ($recipe->updated_at?->diffInMinutes(now()) > 15) {
                DB::transaction(function () use ($recipe, $server) {
                    $mbListings = $this->service->getMarketBoardListings($server, $recipe->itemIDs());
                    $this->service->updateRecipeCosts($recipe, $mbListings);
                    $this->service->getMarketBoardSales($server, $recipe->item_id);
                });
            }
        } else {
            if ($item->updated_at?->diffInMinutes(now()) > 15) {
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
        $sales = Sale::where('item_id', $item->id)->where('timestamp', '>=', Carbon::now()->subDays(7))->latest()->get();
        $aggregatedSales = $this->service->aggregateSales($sales);

        // Listings
        $listings = Listing::where('item_id', $item->id)->orderBy('price_per_unit', 'asc')->get();

        if ($recipe) {
            $recipe->alignAmounts(1);
        }

        return inertia(
            'Recipes',
            [
                "recipe" => $recipe,
                "item" => $item,
                "history" => $aggregatedSales,
                "listings" => $listings,
            ]
        );
    }
}
