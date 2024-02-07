<?php

namespace App\Models;

use App\Http\Controllers\XIVController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe
{
    public int $id;

    public int $item_id;

    public string $name;

    public string $icon;

    /** @var Ingredient[] $ingredients */
    public array $ingredients;

    public float $amount_result;

    public int $market_cost;

    public int $market_craft_cost;

    public int $optimal_craft_cost;

    public int $vendor_cost;

    public string $class_job;

    public string $class_job_icon;

    public static function get($id): ?Recipe
    {
        $recipe = cache()->remember('recipe_' . $id, now()->addMinutes(30), function () use ($id) {
            logger("Fetching recipe {$id}");
            return file_get_contents("https://xivapi.com/recipe/{$id}");
        });

        if ($recipe === false) {
            return null;
        }

        return static::parseJson(json_decode($recipe, true));
    }

    public static function parseJson(array $json): ?Recipe
    {
        $xivController = new XIVController();

        $recipe = new Recipe();
        $recipe->id = $json["ID"];
        $recipe->item_id = $json["ItemResult"]["ID"];
        $recipe->name = $json["Name"];
        $recipe->amount_result = $json["AmountResult"];
        $recipe->vendor_cost = $xivController->getVendorCost($recipe->item_id);
        $recipe->icon = $json["Icon"];
        $recipe->class_job = $json["ClassJob"]["Name"];
        $recipe->class_job_icon = $json["ClassJob"]["Icon"];

        $recipe->ingredients = [];
        for ($i = 0; $i <= 9; $i++) {
            $amount = $json["AmountIngredient{$i}"];
            if ($amount === 0) {
                continue;
            }

            $ingredient = new Ingredient();
            $ingredient->item_id = $json["ItemIngredient{$i}"]["ID"];
            $ingredient->name = $json["ItemIngredient{$i}"]["Name"];
            $ingredient->amount = $amount;
            $ingredient->vendor_cost = $xivController->getVendorCost($ingredient->item_id);
            $ingredient->icon = $json["ItemIngredient{$i}"]["Icon"];
            $ingredient->recipe = null;

            $ingredient_recipe = $json["ItemIngredientRecipe{$i}"];
            if ($ingredient_recipe !== null) {
                $ingredient->recipe = static::get($ingredient_recipe[0]["ID"]);
            }
            $recipe->ingredients[] = $ingredient;
        }

        return $recipe;
    }

    public function alignAmounts(float $target_amount)
    {
        $ratio = $target_amount / $this->amount_result;
        $this->amount_result = $target_amount;

        foreach ($this->ingredients as $ingredient) {
            $ingredient->amount = $ratio * $ingredient->amount;
            if ($ingredient->recipe !== null) {
                $ingredient->recipe->alignAmounts($ingredient->amount);
            }
        }
    }

    public function itemIDs(): array
    {
        $ids = [];
        $ids[] = $this->item_id;
        foreach ($this->ingredients as $ingredient) {
            $ids[] = $ingredient->item_id;
            if ($ingredient->recipe !== null) {
                $ids = array_merge($ids, $ingredient->recipe->itemIDs());
            }
        }

        return $ids;
    }

    public function populateCosts($mb_data)
    {
        $this->market_cost = 0;
        $this->market_craft_cost = 0;
        $this->optimal_craft_cost = 0;

        $mb_item = $mb_data["items"][$this->item_id] ?? null;
        if ($mb_item !== null) {
            $this->market_cost = $this->calculateMarketCost($mb_item);
        }

        foreach ($this->ingredients as &$ingredient) {
            $mb_item = $mb_data["items"][$ingredient->item_id] ?? null;
            if ($mb_item !== null) {
                $ingredient->market_cost = $this->calculateMarketCost($mb_item);
            }

            if ($ingredient->recipe !== null) {
                $ingredient->recipe->populateCosts($mb_data);
            }
        }

        $this->market_craft_cost = $this->calculateCraftCost(false);
        $this->optimal_craft_cost = $this->calculateCraftCost(true);
    }

    private function calculateMarketCost(array $mb_item): int
    {
        $listings = collect($mb_item["listings"])
            ->take(10);

        $median_cost = $listings->median('pricePerUnit');

        $sum = 0;
        foreach ($listings as $listing) {
            $sum += $listing['pricePerUnit'] * $listing['quantity'];
        }
        $avg_cost = $sum / max($listings->sum('quantity'), 1);

        logger("Market cost for item {$mb_item["itemID"]}: avg={$avg_cost}, median={$median_cost}");
        return min($avg_cost, $median_cost) ?? 0;
    }

    private function calculateCraftCost(bool $optimal): int
    {
        $cost = 0;
        foreach ($this->ingredients as $ingredient) {
            $min_cost = $ingredient->market_cost ?? $ingredient->recipe->calculateCraftCost($optimal);
            if ($optimal && $ingredient->recipe !== null) {
                $min_cost = min($min_cost, $ingredient->recipe->calculateCraftCost($optimal));
            }
            if ($optimal && $ingredient->vendor_cost != 0) {
                $min_cost = min($min_cost, $ingredient->vendor_cost);
            }
            $cost += $min_cost * $ingredient->amount;
        }

        return $cost;
    }
}
