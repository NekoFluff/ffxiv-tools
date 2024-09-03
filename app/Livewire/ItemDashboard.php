<?php

namespace App\Livewire;

use App\Jobs\RefreshItem;
use App\Models\Enums\Server;
use App\Models\Item;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'Item Dashboard'])]
class ItemDashboard extends Component
{
    #[Locked]
    public int $id;

    public ?Item $item;

    public Server $server;

    public function mount(int $id): void
    {
        $this->item = Item::find($id);

        $this->server = session('server') ?? Server::GOBLIN;

        $recalculate = boolval(request()->query('recalculate', '0'));

        if ($recalculate || ! $this->item || $this->item->marketPrice($this->server) === null || $this->item->marketPrice($this->server)->updated_at?->diffInMinutes(now()) > 15) {
            RefreshItem::dispatch($id, $this->server);

            return;
        }
    }

    #[On('server-changed')]
    public function updateServer(string $server): void
    {
        $this->server = Server::from($server);
    }

    public function render(): View
    {
        return view('livewire.item-dashboard')
            ->title($this->item?->name ?? 'Invalid Item');
    }
}
