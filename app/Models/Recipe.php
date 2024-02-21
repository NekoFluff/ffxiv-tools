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

    public static function fromXIVRecipeJson(array $json): ?Recipe
    {
        $item = Item::updateOrCreate([
            'id' => intval($json["ItemResult"]["ID"]),
        ], [
            'name' => $json["Name"],
            'icon' => $json["Icon"],
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
        $ids[] = $this->itemID;
        foreach ($this->ingredients as $ingredient) {
            $ids[] = $ingredient->itemID;
            if ($ingredient->craftingRecipe !== null) {
                $ids = array_merge($ids, $ingredient->craftingRecipe->itemIDs());
            }
        }

        return $ids;
    }
}
