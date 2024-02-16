<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Ingredient extends Model
{
    use HasFactory;

    protected $with = [
        'craftingRecipe',
        'item',
    ];

    protected $fillable = [
        'amount',
        'recipe_id',
        'item_id',
    ];

    protected $casts = [
        'amount' => 'integer',
        'recipe_id' => 'integer',
        'item_id' => 'integer',
    ];

    /** @return BelongsTo<Recipe> */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    /** @return BelongsTo<Item> */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /** @return HasOneThrough<Recipe> */
    public function craftingRecipe(): HasOneThrough
    {
        return $this->hasOneThrough(Recipe::class, Item::class, 'id', 'item_id', 'item_id', 'id');
    }
}
