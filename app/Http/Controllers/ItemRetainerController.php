<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Retainer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ItemRetainerController extends Controller
{
    public function store(Request $request, Retainer $retainer): RedirectResponse
    {
        $this->authorize('update', $retainer);

        $validated = $request->validate([
            'item_id' => ['required', 'integer', 'exists:items,id'],
        ]);

        $retainer->items()->syncWithoutDetaching([$validated['item_id']]);

        return back();
    }

    public function destroy(Retainer $retainer, Item $item): RedirectResponse
    {
        $this->authorize('update', $retainer);

        $retainer->items()->detach($item->id);

        return back();
    }
}
