<?php

namespace App\Livewire;

use App\Structures\CraftableItem;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ItemBox extends Component
{
    public CraftableItem $craftableItem;

    public function mount(CraftableItem $craftableItem): void
    {
        $this->craftableItem = $craftableItem;
    }

    public function render(): View
    {
        return view('livewire.item-box');
    }

    #[Computed]
    public function profit(): string
    {
        return number_format($this->craftableItem->market_price * $this->craftableItem->crafting_output_count - $this->craftableItem->optimal_craft_cost, 0);
    }

    #[Computed]
    public function profitRatio(): string
    {
        if ($this->craftableItem->optimal_craft_cost === 0) {
            return 'N/A';
        }

        return number_format((($this->craftableItem->market_price * $this->craftableItem->crafting_output_count) / $this->craftableItem->optimal_craft_cost) * 100 - 100, 2).'%';
    }

    /**
     * @return array<string,string>
     */
    #[Computed]
    public function craftOrBuyColors(): array
    {
        $prices = [
            [
                'value' => $this->craftableItem->purchase_cost,
                'name' => 'Purchase Cost',
            ],
            [
                'value' => $this->craftableItem->market_craft_cost,
                'name' => 'Market Craft Cost',
            ],
            [
                'value' => $this->craftableItem->optimal_craft_cost,
                'name' => 'Optimal Craft Cost',
            ],
        ];

        $prices = collect($prices)->sortBy('value')->values();

        $colors = ['text-green-500', 'text-orange-400', 'text-red-500'];
        $mapping = [];
        foreach ($prices as $index => $price) {
            $mapping[$price['name']] = $colors[$index];
        }

        return $mapping;
    }
}
