<?php

namespace App\Models;

use App\Http\Controllers\XIVController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Recipe extends Model
{
    use HasFactory;

    protected $with = [
        'item',
        'ingredients',
    ];

    protected $fillable = [
        'id',
        'amount_result',
        'purchase_cost',
        'market_craft_cost',
        'optimal_craft_cost',
        'market_price',
        'vendor_price',
        'class_job',
        'class_job_icon',
        'item_id',
    ];

    protected $casts = [
        'id' => 'integer',
        'amount_result' => 'integer',
        'purchase_cost' => 'integer',
        'market_craft_cost' => 'integer',
        'optimal_craft_cost' => 'integer',
        'item_id' => 'integer',
    ];

    public function ingredients(): HasMany
    {
        return $this->hasMany(Ingredient::class);
    }

    /** @return BelongsTo<Item> */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public static function parseJson(array $json): ?Recipe
    {
        $xivController = new XIVController();

        $item = Item::updateOrCreate([
            'id' => intval($json["ItemResult"]["ID"]),
        ], [
            'name' => $json["Name"],
            'icon' => $json["Icon"],
            // TODO: Market Cost
            'market_price' => Item::DEFAULT_MARKET_PRICE,
            'vendor_price' => $xivController->getVendorCost($json["ItemResult"]["ID"]),
        ]);

        $recipe = Recipe::updateOrCreate([
            'id' => $json["ID"],
        ], [
            'amount_result' => $json["AmountResult"],
            'purchase_cost' => 0,
            'market_craft_cost' => 0,
            'optimal_craft_cost' => 0,
            'class_job' => $json["ClassJob"]["Name"],
            'class_job_icon' => $json["ClassJob"]["Icon"],
            'item_id' => $item->id,
        ]);

        for ($i = 0; $i <= 9; $i++) {
            $amount = $json["AmountIngredient{$i}"];
            if ($amount === 0) {
                continue;
            }

            $ingredient_item = Item::updateOrCreate([
                'id' => $json["ItemIngredient{$i}"]["ID"],
            ], [
                'name' => $json["ItemIngredient{$i}"]["Name"],
                'icon' => $json["ItemIngredient{$i}"]["Icon"],
                // TODO: Market Cost
                'market_price' => Item::DEFAULT_MARKET_PRICE,
                'vendor_price' => $xivController->getVendorCost($json["ItemIngredient{$i}"]["ID"]),
            ]);

            Ingredient::updateOrCreate([
                'recipe_id' => $recipe->id,
                'item_id' => $ingredient_item->id,
            ], [
                'amount' => $amount,
            ]);

            $ingredient_recipe = $json["ItemIngredientRecipe{$i}"];
            if ($ingredient_recipe !== null) {
                $xivController->getRecipe($ingredient_recipe[0]["ID"]);
            }
        }

        return $recipe;
    }

    public function alignAmounts(float $target_amount)
    {
        $ratio = $target_amount / $this->amount_result;
        $this->amount_result = $target_amount;

        foreach ($this->ingredients as $ingredient) {
            $ingredient->amount = $ratio * $ingredient->amount;
            if ($ingredient->craftingRecipe !== null) {
                $ingredient->craftingRecipe->alignAmounts($ingredient->amount);
            }
        }

        $this->save();
    }

    public function itemIDs(): array
    {
        $ids = [];
        $ids[] = $this->item_id;
        foreach ($this->ingredients as $ingredient) {
            $ids[] = $ingredient->item_id;
            if ($ingredient->craftingRecipe !== null) {
                $ids = array_merge($ids, $ingredient->craftingRecipe->itemIDs());
            }
        }

        return $ids;
    }

    public function populateCosts($mb_data)
    {
        $mb_item = $mb_data["items"][$this->item_id] ?? null;
        if ($mb_item !== null) {
            $this->item->market_price = $this->calculateMarketPrice($mb_item);
            $this->item->save();
        }

        foreach ($this->ingredients as $ingredient) {
            $mb_item = $mb_data["items"][$ingredient->item_id] ?? null;
            if ($mb_item !== null) {
                $ingredient->item->market_price = $this->calculateMarketPrice($mb_item);
                $ingredient->item->save();
            } else {
                $ingredient->item->market_price = Item::DEFAULT_MARKET_PRICE;
            }

            if ($ingredient->craftingRecipe !== null) {
                $ingredient->craftingRecipe->populateCosts($mb_data);
            }
        }

        $this->calculatePurchaseCost();
        $this->calculateCraftCost(false);
        $this->calculateCraftCost(true);
    }

    private function calculatePurchaseCost()
    {
        $cost = $this->item->market_price;
        if ($this->item->vendor_price != 0) {
            $cost = min($cost, $this->item->vendor_price);
        }
        $this->update([
            'purchase_cost' => $cost * $this->amount_result
        ]);
    }

    private function calculateMarketPrice(array $mb_item)
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
        return min($avg_cost, $median_cost) ?: Item::DEFAULT_MARKET_PRICE;
    }

    private function calculateCraftCost(bool $optimal): int
    {
        $cost = 0;
        foreach ($this->ingredients as $ingredient) {
            $min_ingredient_cost = $ingredient->item->market_price ?: Item::DEFAULT_MARKET_PRICE;
            if (!$ingredient->item->market_price && $ingredient->craftingRecipe !== null) {
                $min_ingredient_cost = $ingredient->craftingRecipe->calculateCraftCost($optimal) / $ingredient->craftingRecipe->amount_result;
            }
            if ($optimal && $ingredient->craftingRecipe !== null) {
                $min_ingredient_cost = min($min_ingredient_cost, $ingredient->craftingRecipe->calculateCraftCost($optimal) / $ingredient->craftingRecipe->amount_result);
            }
            if ($optimal && $ingredient->item->vendor_price != 0) {
                $min_ingredient_cost = min($min_ingredient_cost, $ingredient->item->vendor_price);
            }

            $cost += $min_ingredient_cost * $ingredient->amount;
        }

        if ($optimal) {
            $this->update([
                'optimal_craft_cost' => $cost,
            ]);
        } else {
            $this->update([
                'market_craft_cost' => $cost,
            ]);
        }

        return $cost;
    }
}
