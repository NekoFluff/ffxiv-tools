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
class PriceHistoryChart extends Component
{
    #[Reactive]
    public int $itemID;

    public Server $server;

    /**
     * The average price for the item over the last 7 days.
     *
     * @var array<string,int>
     */
    #[Locked]
    public array $averagePrice;

    /**
     * The min price for the item over the last 7 days.
     *
     * @var array<string,int>
     */
    #[Locked]
    public array $minPrice;

    /**
     * The max price for the item over the last 7 days.
     *
     * @var array<string,int>
     */
    #[Locked]
    public array $maxPrice;

    /**
     * The median price for the item over the last 7 days.
     *
     * @var array<string,int>
     */
    #[Locked]
    public array $medianPrice;

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
        $aggregatedSales = (new AggregateSalesByDay())($sales);
        $this->averagePrice = $aggregatedSales->mapWithKeys(function ($item) {
            return [$item['date'] => $item['avg_price']];
        })->toArray();

        $this->minPrice = $aggregatedSales->mapWithKeys(function ($item) {
            return [$item['date'] => $item['min_price']];
        })->toArray();

        $this->maxPrice = $aggregatedSales->mapWithKeys(function ($item) {
            return [$item['date'] => $item['max_price']];
        })->toArray();

        $this->medianPrice = $aggregatedSales->mapWithKeys(function ($item) {
            return [$item['date'] => $item['median_price']];
        })->toArray();

        $this->dispatch('refresh-price-history-chart');

        return view('livewire.price-history-chart');
    }
}
