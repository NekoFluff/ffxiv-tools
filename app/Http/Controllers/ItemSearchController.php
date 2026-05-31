<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ItemSearchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = $request->validate(['q' => ['required', 'string', 'max:100']])['q'];

        $items = Item::where('name', 'like', '%'.trim($q).'%')
            ->limit(20)
            ->get(['id', 'name', 'icon']);

        return response()->json($items);
    }
}
