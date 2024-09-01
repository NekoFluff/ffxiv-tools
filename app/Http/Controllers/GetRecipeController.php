<?php

namespace App\Http\Controllers;

use App\Models\Enums\Server;
use App\Models\Item;
use App\Models\Listing;
use App\Models\Retainer;
use App\Models\Sale;
use App\Services\FFXIVService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
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
        $server = Server::from(request()->query('server', 'Goblin'));

        $recalculate = boolval(request()->query('recalculate', '0'));
        $itemID = intval($itemID);

        $recipe = $this->service->getRecipeByItemID($itemID);
        $item = $recipe->item ?? Item::find($itemID);
        if ($recipe) {
            $marketPrice = $recipe->item->marketPrice($server);

            if ($recalculate || $marketPrice === null || $marketPrice->updated_at?->diffInMinutes(now()) > 15) {
                DB::transaction(function () use ($recipe, $server) {
                    $this->service->refreshMarketboardListings($server, $recipe->itemIDs());
                    $listings = Listing::whereIn('item_id', $recipe->itemIDs())->get()->groupBy('item_id');
                    $this->service->updateMarketPrices($server, $recipe, $listings);
                    $this->service->updateRecipeCosts($server, $recipe);
                    $this->service->refreshMarketBoardSales($server, $recipe->item_id);
                });
            }
        } else {
            $marketPrice = $item->marketPrice($server);
            if ($item && $marketPrice === null || $marketPrice->updated_at?->diffInMinutes(now()) > 15) {
                DB::transaction(function () use ($item, $server) {
                    $this->service->refreshMarketboardListings($server, [$item->id]);
                    $listings = Listing::where('item_id', $item->id)->get();
                    if (! $listings->isEmpty()) {
                        $this->service->updateMarketPrice($server, $item, $listings);
                    }
                    $this->service->refreshMarketBoardSales($server, $item->id);
                });
            }
        }

        // Sales
        $sales = Sale::fromServer($server)->where('item_id', $itemID)->where('timestamp', '>=', Carbon::now()->subDays(7))->latest()->get();
        $aggregatedSales = $this->service->aggregateSales($sales);

        // Listings
        $listings = Listing::fromServer($server)->where('item_id', $itemID)->orderBy('price_per_unit', 'asc')->get();

        if ($recipe) {
            $recipe->alignAmounts($server, 1);
        }

        $item = $recipe?->item ?? $item;

        $item?->fresh();
        $lastUpdated = $item?->marketPrice($server)?->updated_at?->diffForHumans() ?? '';

        return inertia(
            'Dashboard',
            [
                'canLogin' => Route::has('login'),
                'canRegister' => Route::has('register'),
                'retainers' => Retainer::where('user_id', auth()->id())->where('server', $server)->whereRelation('items', 'id', $recipe?->item->id)->get(),
                'recipe' => $recipe,
                'item' => $item,
                'history' => $aggregatedSales,
                'listings' => $listings,
                'lastUpdated' => $lastUpdated,
            ]
        );
    }
}
