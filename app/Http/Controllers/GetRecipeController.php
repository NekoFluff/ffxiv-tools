<?php

namespace App\Http\Controllers;

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

        if (empty($itemID)) {
            return inertia(
                'Recipes',
                [
                    "recipe" => null,
                    "history" => [],
                    "listings" => [],
                ]
            );
        }

        $recipe = $this->service->getRecipeByItemID($itemID);
        if (!$recipe) {
            return inertia(
                'Recipes',
                [
                    "recipe" => null,
                    "history" => [],
                    "listings" => [],
                ]
            );
        }

        $recipe->alignAmounts(1);
        if ($recipe->updated_at->diffInMinutes(now()) > 15) {
            DB::transaction(function () use ($recipe, $server) {
                $mbListings = $this->service->getMarketBoardListings($server, $recipe->itemIDs());
                $this->service->updateRecipeCosts($recipe, $mbListings);
                $this->service->getMarketBoardSales($server, $recipe->item_id);
            });
        }

        // Sales
        $sales = Sale::where('item_id', $itemID)->where('timestamp', '>=', Carbon::now()->subDays(7))->latest()->get();
        $aggregatedSales = $this->service->aggregateSales($sales);

        // Listings
        $listings = Listing::where('item_id', $itemID)->orderBy('price_per_unit', 'asc')->get();

        return inertia(
            'Recipes',
            [
                "recipe" => $recipe,
                "history" => $aggregatedSales,
                "listings" => $listings,
            ]
        );
    }
}
