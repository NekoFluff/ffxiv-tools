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

    /** @return BelongsTo<Item, Recipe> */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function alignAmounts(float $target_amount)
    {
        $ratio = $target_amount / $this->amount_result;
        $this->amount_result = $target_amount;

        $this->purchase_cost = intval($this->purchase_cost * $ratio);
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
