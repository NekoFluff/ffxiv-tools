<?php

namespace App\Livewire;

use App\Models\Enums\Server;
use App\Models\Listing;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Livewire\Component;

#[Lazy(isolate: false)]
class CurrentListingsTable extends Component
{
    #[Reactive]
    public int $itemID;

    public ?Server $server = null;

    /**
     * The listings for the item.
     *
     * @var array<string,mixed>
     */
    #[Locked]
    public array $listings;

    public function mount(int $itemID): void
    {
        $this->itemID = $itemID;

        $this->server = session('server') ?? Server::GOBLIN;
    }

    #[On('server-changed')]
    public function updateServer(string $server): void
    {
        $this->server = Server::from($server);
    }

    public function render(): View
    {
        if (! $this->server) {
            $this->server = session('server') ?? Server::GOBLIN;
        }

        $this->listings = Listing::fromServer($this->server)->where('item_id', $this->itemID)->orderBy('price_per_unit', 'asc')->limit(10)->get()->toArray();

        return view('livewire.current-listings-table');
    }
}
