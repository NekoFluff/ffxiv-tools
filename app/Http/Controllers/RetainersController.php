<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestroyRetainerRequest;
use App\Http\Requests\StoreRetainerRequest;
use App\Models\Enums\Server;
use App\Models\Listing;
use App\Models\Retainer;
use App\Services\FFXIVService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RetainersController extends Controller
{
    private FFXIVService $service;

    public function __construct(FFXIVService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $retainers = $user->retainers()->with('items', 'items.marketPrices')->get();

        $itemIDsByServer = $retainers->groupBy('server')->map(function ($retainers) {
            return $retainers->pluck('items')->flatten()->pluck('id')->toArray();
        })->toArray();

        $listings = [];
        foreach ($itemIDsByServer as $server => $ids) {
            //TODO: Conditionally refresh marketboard listings
            $this->service->refreshMarketboardListings($server, $ids);
        }

        $listings = Listing::whereIn('item_id', collect($itemIDsByServer)->flatten())->get()->groupBy('server')->map(function ($listings) {
            return $listings->groupBy('item_id')->map(function ($listings) {
                return $listings->sortBy('price_per_unit')->values()->toArray();
            });
        });

        $resp = [];
        foreach ($retainers as $retainer) {
            $retainerResp = [
                'retainer_id' => $retainer->id,
                'retainer_name' => $retainer->name,
                'server' => $retainer->server,
                'items' => [],
            ];
            foreach ($retainer->items as $item) {
                $retainerListings = array_values(array_filter($listings[$retainer->server][$item->id] ?? [], function ($listing) use ($retainer) {
                    return $listing['retainer_name'] === $retainer->name;
                }));
                $retainerResp['items'][] = [
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'retainer_listing_price' => $retainerListings ? $retainerListings[0]['price_per_unit'] : null,
                    'num_retainer_listings' => $retainerListings ? count($retainerListings) : 0,
                    'lowest_listing_price' => $listings[$retainer->server][$item->id][0]['price_per_unit'] ?? null,
                ];
            }
            $resp[] = $retainerResp;
        }

        return response()->json($resp);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRetainerRequest $request)
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var Retainer $retainer */
        $retainer = $user->retainers()->create([
            'name' => $request->input('name'),
            'data_center' => Server::from($request->input('server'))->dataCenter(),
            'server' => Server::from($request->input('server')),
        ]);

        return response()->json([
            'retainer_id' => $retainer->id,
            'retainer_name' => $retainer->name,
            'server' => $retainer->server,
            'items' => [],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Retainer $retainer)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Retainer $retainer)
    {

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DestroyRetainerRequest $request)
    {
        $retainer = Retainer::find($request->route('retainerID'));
        $retainer->delete();

        return response()->noContent();
    }
}
