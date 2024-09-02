<?php

namespace App\Livewire;

use App\Models\Enums\Server;
use App\Models\Item;
use App\Structures\CraftableItem;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class ItemBoxes extends Component
{
    public Item $item;

    public CraftableItem $craftableItem;

    public function mount(Item $item): void
    {
        $this->item = $item;
    }

    #[Computed]
    public function craftableItem(): ?CraftableItem
    {
        return Cache::remember('craftableItem.'.$this->item->id, 1, function () {
            return $this->item->recipe ? CraftableItem::fromRecipe($this->item->recipe, Server::GOBLIN, 1) : null;
        });
    }

    public function placeholder(): View
    {
        return view('livewire.placeholders.item-boxes');
    }

    public function render(): View
    {
        return view('livewire.item-boxes');
    }
}
