<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

/**
 * App\Models\Ingredient
 *
 * @property int $id
 * @property float $amount
 * @property int $recipe_id
 * @property int $item_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Recipe|null $craftingRecipe
 * @property-read \App\Models\Item $item
 * @property-read \App\Models\Recipe $recipe
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Ingredient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ingredient newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ingredient query()
 * @method static \Illuminate\Database\Eloquent\Builder|Ingredient whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ingredient whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ingredient whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ingredient whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ingredient whereRecipeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ingredient whereUpdatedAt($value)
 * @method static \Database\Factories\IngredientFactory factory($count = null, $state = [])
 *
 * @mixin \Eloquent
 */
class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'recipe_id',
        'item_id',
    ];

    protected $casts = [
        'recipe_id' => 'integer',
        'item_id' => 'integer',
    ];

    /** @return BelongsTo<Recipe, self> */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    /** @return BelongsTo<Item, self> */
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
