<?php

namespace App\Http\Controllers;

use App\Actions\AggregateSalesByDay;
use App\Jobs\RefreshItem;
use App\Models\Enums\Server;
use App\Models\Item;
use App\Models\Listing;
use App\Models\Sale;
use App\Structures\CraftableItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class ItemController extends Controller
{
    public function show(int $id): Response
    {
        $item = Item::find($id);
        $serverValue = session('server', Server::GOBLIN->value);
        $server = Server::from($serverValue);

        if ($item) {
            $marketPrice = $item->marketPrice($server);
            if ($marketPrice === null || $marketPrice->updated_at?->diffInMinutes(now()) > 15) {
                RefreshItem::dispatch($id, $server);
            }
        }

        $craftableItem = $item && $item->recipe
            ? Cache::remember("craftableItem.{$item->id}.{$server->value}", 60, fn () => CraftableItem::fromRecipe($item->recipe, $server, 1)->toArray())
            : null;

        $listings = $item
            ? Listing::fromServer($server)->where('item_id', $item->id)->orderBy('price_per_unit')->limit(10)->get()
            : collect();

        $sales = $item
            ? Sale::fromServer($server)->where('item_id', $item->id)->where('timestamp', '>=', Carbon::now()->subDays(7))->latest()->get()
            : collect();

        $aggregated = (new AggregateSalesByDay())($sales);

        return Inertia::render('Items/Show', [
            'item' => $item,
            'server' => $serverValue,
            'servers' => Server::all(),
            'craftableItem' => $craftableItem,
            'listings' => $listings,
            'priceHistory' => [
                'average' => $aggregated->pluck('avg_price', 'date'),
                'min' => $aggregated->pluck('min_price', 'date'),
                'max' => $aggregated->pluck('max_price', 'date'),
                'median' => $aggregated->pluck('median_price', 'date'),
            ],
            'quantitySold' => $aggregated->pluck('quantity', 'date'),
        ]);
    }
}
