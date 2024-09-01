<?php

namespace App\Structures;

use App\Models\CraftingCost;
use App\Models\Enums\Server;
use App\Models\Ingredient;
use App\Models\MarketPrice;
use App\Models\Recipe;
use Illuminate\Support\Collection;
use Livewire\Wireable;

class CraftableItem implements Wireable
{
    public string $name;

    public int $item_id;

    public string $icon;

    /** @var Collection<int,CraftableItem> */
    public Collection $crafting_materials;

    public float $crafting_output_count = -1;

    public float $number_needed_per_craft = -1;

    public string $class_job = 'Unknown';

    public int $class_job_level = -1;

    public string $class_job_icon = '';

    public int $purchase_cost = -1;

    public int $market_craft_cost = -1;

    public int $optimal_craft_cost = -1;

    public int $market_price = MarketPrice::DEFAULT_MARKET_PRICE;

    public int $market_price_updated_at;

    public int $vendor_price = -1;

    public static function fromRecipe(Recipe $recipe, Server $server, float $target_amount): CraftableItem
    {
        $ratio = $target_amount / $recipe->amount_result;

        /** @var CraftingCost */
        $craftingCost = $recipe->craftingCost($server);

        $item = new CraftableItem();
        $item->name = $recipe->item->name;
        $item->item_id = $recipe->item->id;
        $item->icon = $recipe->item->icon;
        $item->crafting_output_count = $target_amount;
        $item->number_needed_per_craft = $ratio * $recipe->amount_result;
        $item->crafting_materials = $recipe->ingredients->map(
            fn ($ingredient) => $ingredient->craftingRecipe
            ? CraftableItem::fromRecipe($ingredient->craftingRecipe, $server, $ingredient->amount * $ratio)
            : CraftableItem::fromIngredient($ingredient, $server, $ingredient->amount * $ratio)
        );

        $item->class_job = $recipe->class_job;
        $item->class_job_level = $recipe->class_job_level;
        $item->class_job_icon = $recipe->class_job_icon;

        $item->purchase_cost = intval($craftingCost->purchase_cost * $target_amount);
        $item->market_craft_cost = intval($craftingCost->market_craft_cost * $ratio);
        $item->optimal_craft_cost = intval($craftingCost->optimal_craft_cost * $ratio);
        $item->market_price = $recipe->item->marketPrice($server)?->price ?: MarketPrice::DEFAULT_MARKET_PRICE;
        $item->market_price_updated_at = intval($recipe->item->marketPrice($server)?->updated_at?->timestamp);
        $item->vendor_price = $recipe->item->vendor_price;

        return $item;
    }

    public static function fromIngredient(Ingredient $ingredient, Server $server, float $target_amount): CraftableItem
    {
        $item = new CraftableItem();
        $item->name = $ingredient->item->name;
        $item->item_id = $ingredient->item->id;
        $item->icon = $ingredient->item->icon;
        $item->crafting_output_count = $target_amount;
        $item->crafting_materials = collect();

        $item->purchase_cost = 0;
        $item->market_craft_cost = 0;
        $item->optimal_craft_cost = 0;
        $item->market_price = $ingredient->item->marketPrice($server)?->price ?: MarketPrice::DEFAULT_MARKET_PRICE;
        $item->market_price_updated_at = intval($ingredient->item->marketPrice($server)?->updated_at?->timestamp);
        $item->vendor_price = $ingredient->item->vendor_price;

        return $item;
    }

    /**
     * @return array<string, mixed>
     */
    public function toLivewire(): array
    {
        return [
            'name' => $this->name,
            'item_id' => $this->item_id,
            'icon' => $this->icon,
            'crafting_materials' => $this->crafting_materials->map(fn ($item) => $item->toLivewire()),
            'crafting_output_count' => $this->crafting_output_count,
            'number_needed_per_craft' => $this->number_needed_per_craft,
            'class_job' => $this->class_job,
            'class_job_level' => $this->class_job_level,
            'class_job_icon' => $this->class_job_icon,
            'purchase_cost' => $this->purchase_cost,
            'market_craft_cost' => $this->market_craft_cost,
            'optimal_craft_cost' => $this->optimal_craft_cost,
            'market_price' => $this->market_price,
            'market_price_updated_at' => $this->market_price_updated_at,
            'vendor_price' => $this->vendor_price,
        ];
    }

    public static function fromLivewire(mixed $value): CraftableItem
    {
        $item = new CraftableItem();
        $item->name = $value['name'];
        $item->item_id = $value['item_id'];
        $item->icon = $value['icon'];
        $item->crafting_materials = collect(array_map(fn (mixed $item) => CraftableItem::fromLivewire($item), $value['crafting_materials']));
        $item->crafting_output_count = $value['crafting_output_count'];
        $item->number_needed_per_craft = $value['number_needed_per_craft'];
        $item->class_job = $value['class_job'];
        $item->class_job_level = $value['class_job_level'];
        $item->class_job_icon = $value['class_job_icon'];
        $item->purchase_cost = $value['purchase_cost'];
        $item->market_craft_cost = $value['market_craft_cost'];
        $item->optimal_craft_cost = $value['optimal_craft_cost'];
        $item->market_price = $value['market_price'];
        $item->market_price_updated_at = $value['market_price_updated_at'];
        $item->vendor_price = $value['vendor_price'];

        return $item;
    }
}
