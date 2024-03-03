<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Listing;
use App\Models\Sale;
use App\Services\FFXIVService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Response;
use Inertia\ResponseFactory;

class GetRecipeController extends Controller
{
    private FFXIVService $service;

    public function __construct(FFXIVService $service)
    {
        $this->service = $service;
    }

    public function __invoke(int $itemID): Response|ResponseFactory
    {
        $recalculate = boolval(request()->query('recalculate', '0'));
        $server = 'Goblin';
        $itemID = intval($itemID);

        $recipe = $this->service->getRecipeByItemID($itemID);
        $item = $recipe->item ?? Item::find($itemID);
        if ($recipe) {
            if ($recalculate || $recipe->updated_at?->diffInMinutes(now()) > 15) {
                DB::transaction(function () use ($recipe, $server) {
                    $this->service->refreshMarketboardListings($server, $recipe->itemIDs());
                    $listings = Listing::whereIn('item_id', $recipe->itemIDs())->get()->groupBy('item_id');
                    $this->service->updateMarketPrices($recipe, $listings);
                    $this->service->updateRecipeCosts($recipe);
                    $this->service->refreshMarketBoardSales($server, $recipe->item_id);
                });
            }
        } else {
            if ($item && $item->updated_at?->diffInMinutes(now()) > 15) {
                DB::transaction(function () use ($item, $server) {
                    $this->service->refreshMarketboardListings($server, [$item->id]);
                    $listings = Listing::where('item_id', $item->id)->get();
                    if (! $listings->isEmpty()) {
                        $this->service->updateMarketPrice($item, $listings);
                    }
                    $this->service->refreshMarketBoardSales($server, $item->id);
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

        $lastUpdated = $recipe?->updated_at?->diffForHumans() ?? $item?->updated_at?->diffForHumans() ?? '';

        return inertia(
            'Recipe',
            [
                'recipe' => $recipe,
                'item' => $recipe?->item ?? $item ?? null,
                'history' => $aggregatedSales,
                'listings' => $listings,
                'lastUpdated' => $lastUpdated,
            ]
        );
    }
}
