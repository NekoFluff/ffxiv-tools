<?php

namespace App\Models;

use App\Http\Controllers\XIVController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Collection;

class Recipe extends Model
{
    use HasFactory;

    protected $with = [
        'item',
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
        'class_job_level',
        'class_job_icon',
        'item_id',
    ];

    protected $casts = [
        'id' => 'integer',
        'purchase_cost' => 'integer',
        'market_craft_cost' => 'integer',
        'optimal_craft_cost' => 'integer',
        'item_id' => 'integer',
    ];

    /** @return HasMany<Ingredient> */
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
            'vendor_price' => $xivController->getVendorCost($json["ItemResult"]["ID"]),
        ]);

        $recipe = Recipe::updateOrCreate([
            'id' => $json["ID"],
        ], [
            'amount_result' => $json["AmountResult"],
            'purchase_cost' => 0,
            'market_craft_cost' => 0,
            'optimal_craft_cost' => 0,
            'class_job' => $json["ClassJob"]["NameEnglish"],
            'class_job_level' => $json["RecipeLevelTable"]["ClassJobLevel"],
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
                'vendor_price' => $xivController->getVendorCost($json["ItemIngredient{$i}"]["ID"]),
            ]);

            Ingredient::updateOrCreate([
                'recipe_id' => $recipe->id,
                'item_id' => $ingredient_item->id,
            ], [
                'amount' => $amount,
            ]);

            $ingredient_recipe_id = $json["ItemIngredientRecipe{$i}"][0]["ID"] ?? null;
            if ($ingredient_recipe_id !== null) {
                $ingredient_recipe = Recipe::where('id', $ingredient_recipe_id)->first();
                if ($ingredient_recipe === null) {
                    $xivController->getRecipe($ingredient_recipe_id);
                }
            }
        }

        return $recipe;
    }

    public function alignAmounts(float $target_amount)
    {
        $ratio = $target_amount / $this->amount_result;
        $this->amount_result = $target_amount;

        $this->purchase_cost = $this->purchase_cost * $ratio;
        $this->market_craft_cost = $this->market_craft_cost * $ratio;
        $this->optimal_craft_cost = $this->optimal_craft_cost * $ratio;
        foreach ($this->ingredients as $ingredient) {
            $ingredient->amount = $ratio * $ingredient->amount;
            if ($ingredient->craftingRecipe !== null) {
                $ingredient->craftingRecipe->alignAmounts($ingredient->amount);
            }
        }
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


    /**
     *  @param array<int, Collection<Listing>> $mb_data
    */
    public function populateCosts(array $mb_data)
    {
        $listings = $mb_data[$this->item_id] ?? null;
        if ($listings !== null) {
            $this->item->update([
                'market_price' => $this->calculateMarketPrice($listings),
            ]);
        }

        foreach ($this->ingredients as $ingredient) {
            $listings = $mb_data[$ingredient->item_id] ?? collect([]);
            if ($listings !== null) {
                $ingredient->item->update([
                    'market_price' => $this->calculateMarketPrice($listings),
                ]);
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

    /**
     * @param Collection<Listing> $listings
     */
    private function calculateMarketPrice(Collection $listings)
    {
        if ($listings->isEmpty()) {
            return Item::DEFAULT_MARKET_PRICE;
        }

        $listings = collect($listings)->take(10);

        $median_cost = $listings->median('price_per_unit');

        $sum = 0;
        foreach ($listings as $listing) {
            $sum += $listing['price_per_unit'] * $listing['quantity'];
        }
        $avg_cost = $sum / max($listings->sum('quantity'), 1);

        // logger("Listings for item {$listings[0]->item->id}: " . json_encode($listings->toArray()));
        // logger("Market cost for item {$listings[0]->item->id}: avg={$avg_cost}, median={$median_cost}");
        return min($avg_cost, $median_cost) ?: Item::DEFAULT_MARKET_PRICE;
    }

    private function calculateCraftCost(bool $optimal): int
    {
        $cost = 0;

        foreach ($this->ingredients as $ingredient) {
            $min_ingredient_cost = $ingredient->item->market_price ?: Item::DEFAULT_MARKET_PRICE;
            if (!$ingredient->item->market_price && $ingredient->craftingRecipe !== null) {
                $min_ingredient_cost = $ingredient->craftingRecipe->calculateCraftCost($optimal, $ingredient->amount) / $ingredient->craftingRecipe->amount_result;
            }
            if ($optimal && $ingredient->craftingRecipe !== null) {
                $min_ingredient_cost = min($min_ingredient_cost, $ingredient->craftingRecipe->calculateCraftCost($optimal, $ingredient->amount) / $ingredient->craftingRecipe->amount_result);
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
