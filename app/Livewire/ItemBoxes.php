<?php

namespace App\Livewire;

use App\Models\Enums\Server;
use App\Models\Item;
use App\Structures\CraftableItem;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use Livewire\Component;

#[Lazy(isolate: false)]
class ItemBoxes extends Component
{
    public Item $item;

    public Server $server;

    public function mount(Item $item): void
    {
        $this->item = $item;

        $this->server = session('server') ?? Server::GOBLIN;
    }

    #[Computed]
    public function craftableItem(): ?CraftableItem
    {
        return Cache::remember('craftableItem.'.$this->item->id.'.'.$this->server->value, 60, function () {
            return $this->item->recipe ? CraftableItem::fromRecipe($this->item->recipe, $this->server, 1) : null;
        });
    }

    #[On('server-changed')]
    public function updateServer(string $server): void
    {
        $this->server = Server::from($server);
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
