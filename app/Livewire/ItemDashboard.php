<?php

namespace App\Livewire;

use App\Jobs\RefreshItem;
use App\Models\Enums\Server;
use App\Models\Item;
use Illuminate\Contracts\View\View;
use Laravel\Telescope\Telescope;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

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
        Log::info('Recalculate: '.$recalculate);
        Log::info('Server'. $this->server->name);
        Log::info('Diff in minutes: '.$this->item->marketPrice($this->server)?->updated_at?->diffInMinutes(now()));
        if ($recalculate || ! $this->item || $this->item->marketPrice($this->server) === null || $this->item->marketPrice($this->server)->updated_at?->diffInMinutes(now()) > 15) {
            Log::info('Dispatch: '.$this->item);
            RefreshItem::dispatch($id, $this->server);
        }
    }

    #[On('server-changed')]
    public function updateServer(string $server): void
    {
        $this->server = Server::from($server);

        if ($this->item && ($this->item->marketPrice($this->server) === null || $this->item->marketPrice($this->server)->updated_at?->diffInMinutes(now()) > 15)) {
            Telescope::startRecording();
            RefreshItem::dispatch($this->item->id, $this->server);
            Telescope::stopRecording();
        }
    }

    public function render(): View
    {
        return view('livewire.item-dashboard')
            ->title($this->item?->name ?? 'Invalid Item');
    }
}
