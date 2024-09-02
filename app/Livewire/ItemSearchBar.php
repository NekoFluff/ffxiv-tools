<?php

namespace App\Livewire;

use App\Models\Item;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class ItemSearchBar extends Component
{
    public string $search = '';

    /** @var Collection<int,Item> */
    public Collection $items;

    public function mount(): void
    {
        $this->items = collect();
    }

    public function updatedSearch(): void
    {
        $this->items = Item::where('name', 'like', '%'.trim(htmlspecialchars($this->search)).'%')->limit(20)->get();
    }

    public function render(): View
    {
        return view('livewire.item-search-bar');
    }
}
