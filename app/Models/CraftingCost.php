<?php

namespace App\Models;

use App\Models\Enums\DataCenter;
use App\Models\Enums\Server;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Database\Factories\CraftingCostFactory;

/**
 * @property int $id
 * @property int $recipe_id
 * @property DataCenter $data_center
 * @property Server $server
 * @property int $purchase_cost
 * @property int $market_craft_cost
 * @property int $optimal_craft_cost
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Recipe $recipe
 *
 * @method static \Illuminate\Database\Eloquent\Builder|CraftingCost newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CraftingCost newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CraftingCost query()
 * @method static \Illuminate\Database\Eloquent\Builder|CraftingCost whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CraftingCost whereDataCenter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CraftingCost whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CraftingCost whereMarketCraftCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CraftingCost whereOptimalCraftCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CraftingCost wherePurchaseCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CraftingCost whereRecipeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CraftingCost whereServer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CraftingCost whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class CraftingCost extends Model
{
    /** @use HasFactory<CraftingCostFactory> */
    use HasFactory;

    protected $fillable = [
        'recipe_id',
        'data_center',
        'server',
        'purchase_cost',
        'market_craft_cost',
        'optimal_craft_cost',
    ];

    protected $casts = [
        'id' => 'integer',
        'purchase_cost' => 'integer',
        'market_craft_cost' => 'integer',
        'optimal_craft_cost' => 'integer',
        'recipe_id' => 'integer',
        'server' => Server::class,
        'data_center' => DataCenter::class,
    ];

    protected $attributes = [
        'purchase_cost' => 0,
        'market_craft_cost' => 0,
        'optimal_craft_cost' => 0,
    ];

    /** @return BelongsTo<Recipe, self> */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }
}
