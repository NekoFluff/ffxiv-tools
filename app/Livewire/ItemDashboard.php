<?php

namespace App\Livewire;

use App\Jobs\RefreshItem;
use App\Models\Enums\Server;
use App\Models\Item;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'Item Dashboard'])]
class ItemDashboard extends Component
{
    #[Locked]
    public int $id;

    public ?Item $item;

    public function mount(int $id): void
    {
        $this->item = Item::find($id);

        $server = request()->query('server', 'Goblin');
        if (is_array($server)) {
            $server = Server::from($server[0]);
        } else {
            $server = Server::from($server);
        }

        $recalculate = boolval(request()->query('recalculate', '0'));

        if ($recalculate || ! $this->item || $this->item->marketPrice($server) === null || $this->item->marketPrice($server)->updated_at?->diffInMinutes(now()) > 15) {
            RefreshItem::dispatch($id, $server);

            return;
        }
    }

    public function render(): View
    {
        return view('livewire.item-dashboard')
            ->title($this->item?->name ?? 'Invalid Item');
    }
}
