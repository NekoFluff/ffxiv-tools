<?php

namespace App\Livewire;

use App\Structures\CraftableItem;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class ItemBox extends Component
{
    #[Reactive]
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
        $totalProfit = $this->craftableItem->market_price * $this->craftableItem->crafting_output_count - $this->craftableItem->optimal_craft_cost;

        return number_format($totalProfit, 0).' gil ('.number_format($totalProfit / $this->craftableItem->crafting_output_count, 0).' ea.)';
    }

    #[Computed]
    public function profitRatio(): string
    {
        if ($this->craftableItem->optimal_craft_cost === 0) {
            return 'N/A';
        }

        return number_format((($this->craftableItem->market_price * $this->craftableItem->crafting_output_count) / $this->craftableItem->optimal_craft_cost) * 100 - 100, 2).'%';
    }

    public function jobToBackgroundColor(string $job): string
    {
        $colors = [
            'Alchemist' => 'red',
            'Armorer' => 'slate',
            'Blacksmith' => 'slate',
            'Carpenter' => 'amber',
            'Culinarian' => 'red',
            'Goldsmith' => 'yellow',
            'Leatherworker' => 'amber',
            'Weaver' => 'indigo',
            'Botanist' => 'green',
            'Fisher' => 'blue',
            'Miner' => 'purple',
        ];

        return $colors[$job] ?? 'slate';
    }

    public function levelToBackgroundColor(int $level): string
    {
        if ($level < 0) {
            return 'slate';
        } elseif ($level <= 50) {
            return 'blue';
        } elseif ($level <= 60) {
            return 'green';
        } elseif ($level <= 70) {
            return 'yellow';
        } elseif ($level <= 80) {
            return 'indigo';
        } elseif ($level <= 90) {
            return 'purple';
        } elseif ($level <= 100) {
            return 'red';
        } else {
            return 'red';
        }
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
