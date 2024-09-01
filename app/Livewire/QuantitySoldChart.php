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
class QuantitySoldChart extends Component
{
    /**
     * The quantity sold for the item over the last 7 days.
     *
     * @var array<string,int>
     */
    #[Locked]
    public array $quantitySold;

    public function mount(int $itemID, string $server): void
    {
        $sales = Sale::fromServer(Server::from($server))->where('item_id', $itemID)->where('timestamp', '>=', Carbon::now()->subDays(7))->latest()->get();
        $this->quantitySold = (new AggregateSalesByDay())($sales)->mapWithKeys(function ($item) {
            return [$item['date'] => $item['quantity']];
        })->toArray();
    }

    public function render(): View
    {
        return view('livewire.quantity-sold-chart', [
            'quantitySold' => $this->quantitySold,
        ]);
    }
}
