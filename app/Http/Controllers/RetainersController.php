<?php

namespace App\Http\Controllers;

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
            //TODO: Look at the GetRecipeController... Need to also conditionally refresh marketboard listings every X minutes
            $listings[$server] = $this->service->fetchMarketboardListings($server, $ids);

        }

        // $listings = Listing::fromServer($server)->where('item_id', $itemID)->orderBy('price_per_unit', 'asc')->get();


        $resp = [];
        foreach ($retainers as $retainer) {
            $retainerResp = [
                'retainer_name' => $retainer->name,
                'server' => $retainer->server,
                'items' => [],
            ];
            foreach ($retainer->items as $item) {
                $retainerResp['items'][] = [
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'listings' => array_filter($listings[$retainer->server][$item->id]['listings'] ?? [], function ($listing) use ($retainer) {
                        return $listing['retainerName'] === $retainer->name;
                    }),
                    'cheapest_listing' => $listings[$retainer->server][$item->id]['listings'][0] ?? null,
                ];
            }
            $resp[] = $retainerResp;
        }

        return response()->json([
            'retainers' => $resp,
            'listings' => $listings,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Retainer $retainer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Retainer $retainer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Retainer $retainer)
    {
        //
    }
}
