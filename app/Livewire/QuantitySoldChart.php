<?php

namespace App\Livewire;

use App\Actions\AggregateSalesByDay;
use App\Models\Enums\Server;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Livewire\Component;

#[Lazy(isolate: false)]
class QuantitySoldChart extends Component
{
    #[Reactive]
    public int $itemID;

    public Server $server;

    /** @var array<string,int> */
    #[Locked]
    public array $quantitySold;

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
        $sales = Sale::fromServer($this->server)->where('item_id', $this->itemID)->where('timestamp', '>=', Carbon::now()->subDays(7))->latest()->get();
        $this->quantitySold = (new AggregateSalesByDay())($sales)->mapWithKeys(function ($sale) {
            return [$sale['date'] => $sale['quantity']];
        })->toArray();

        $this->dispatch('refresh-quantity-sold-chart');

        return view('livewire.quantity-sold-chart');
    }
}
