<?php

namespace App\Livewire;

use App\Actions\AggregateSalesByDay;
use App\Models\Enums\Server;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Lazy]
class PriceHistoryChart extends Component
{
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

    public function mount(int $itemID, string $server): void
    {
        $sales = Sale::fromServer(Server::from($server))->where('item_id', $itemID)->where('timestamp', '>=', Carbon::now()->subDays(7))->latest()->get();
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
    }

    public function render(): View
    {
        return view('livewire.price-history-chart', [
            'averagePrice' => $this->averagePrice,
            'minPrice' => $this->minPrice,
            'maxPrice' => $this->maxPrice,
            'medianPrice' => $this->medianPrice,
        ]);
    }
}
