<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestroyItemRetainersRequest;
use App\Http\Requests\StoreItemRetainerRequest;
use App\Models\Listing;
use App\Models\Retainer;
use App\Services\FFXIVService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ItemRetainerController extends Controller
{
    private FFXIVService $service;

    public function __construct(FFXIVService $service)
    {
        $this->service = $service;
    }

    public function store(StoreItemRetainerRequest $request): JsonResponse
    {
        /** @var Retainer $retainer */
        $retainer = Retainer::find($request->route('retainerID'));
        $retainer->items()->attach($request->input('item_id'));

        $retainerListings = $this->getListingsForRetainer($retainer, $request->input('item_id'));

        if (empty($retainerListings) || $retainerListings[0]['updated_at'] < now()->subHours(1)) {
            $this->service->refreshMarketboardListings($retainer->server, [$request->input('item_id')]);
            $retainerListings = $this->getListingsForRetainer($retainer, $request->input('item_id'));
        }

        return response()->json([
            'retainer_id' => $retainer->id,
            'retainer_name' => $retainer->name,
            'server' => $retainer->server,
            'items' => $retainer->items()->where('id', $request->input('item_id'))->get()->map(function ($item) use ($retainerListings) {
                $lowestListingPrice = Listing::where('item_id', $item->id)->orderBy('price_per_unit', 'asc')->first()?->price_per_unit;
                return [
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'retainer_listing_price' => $retainerListings ? $retainerListings[0]['price_per_unit'] : null,
                    'num_retainer_listings' => $retainerListings ? count($retainerListings) : 0,
                    'lowest_listing_price' => $lowestListingPrice,
                ];
            }),
        ]);
    }

    public function destroy(DestroyItemRetainersRequest $request): Response
    {
        $retainer = Retainer::find($request->route('retainerID'));
        $retainer->items()->detach($request->input('item_ids'));

        return response()->noContent();
    }

    /**
     * Get the listings for the retainer.
     *
     * @return array<Listing>
     */
    private function getListingsForRetainer(Retainer $retainer, int $itemID): array
    {
        return $retainer->load('listings')->listings->filter(function ($listing) use ($itemID) {
            return $listing->item_id === $itemID;
        })->values()->toArray();
    }
}
