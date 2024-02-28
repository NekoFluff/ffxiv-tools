<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Recipe
 *
 * @property int $id
 * @property float $amount_result
 * @property int $purchase_cost
 * @property int $market_craft_cost
 * @property int $optimal_craft_cost
 * @property string $class_job
 * @property int $class_job_level
 * @property string $class_job_icon
 * @property int $item_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Ingredient> $ingredients
 * @property-read int|null $ingredients_count
 * @property-read \App\Models\Item $item
 * @method static \Illuminate\Database\Eloquent\Builder|Recipe newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Recipe newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Recipe query()
 * @method static \Illuminate\Database\Eloquent\Builder|Recipe whereAmountResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Recipe whereClassJob($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Recipe whereClassJobIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Recipe whereClassJobLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Recipe whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Recipe whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Recipe whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Recipe whereMarketCraftCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Recipe whereOptimalCraftCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Recipe wherePurchaseCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Recipe whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Recipe extends Model
{
    use HasFactory;

    protected $with = [
        'item',
        'ingredients',
        'ingredients.item',
        'ingredients.craftingRecipe',
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

    /** @return BelongsTo<Item, Recipe> */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function alignAmounts(float $target_amount): void
    {
        $ratio = $target_amount / $this->amount_result;
        $this->amount_result = $target_amount;

        $this->purchase_cost = intval($this->item->market_price * $target_amount);
        $this->market_craft_cost = intval($this->market_craft_cost * $ratio);
        $this->optimal_craft_cost = intval($this->optimal_craft_cost * $ratio);
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
}
