<?php

namespace App\Livewire;

use App\Models\Enums\Server;
use App\Models\Listing;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Lazy]
class CurrentListingsTable extends Component
{
    /**
     * The listings for the item.
     *
     * @var array<string,mixed>
     */
    #[Locked]
    public array $listings;

    public function mount(int $itemID, string $server): void
    {
        $this->listings = Listing::fromServer(Server::from($server))->where('item_id', $itemID)->orderBy('price_per_unit', 'asc')->limit(10)->get()->toArray();
    }

    public function render(): View
    {
        return view('livewire.current-listings-table');
    }
}
