<?php

namespace App\Models;

use App\Models\Enums\Server;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Recipe
 *
 * @property int $id
 * @property float $amount_result
 * @property string $class_job
 * @property int $class_job_level
 * @property string $class_job_icon
 * @property int $item_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Ingredient> $ingredients
 * @property-read int|null $ingredients_count
 * @property-read \App\Models\Item $item
 *
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
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CraftingCost> $craftingCosts
 * @property-read int|null $crafting_costs_count
 *
 * @method static \Database\Factories\RecipeFactory factory($count = null, $state = [])
 *
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
        'craftingCosts',
    ];

    protected $fillable = [
        'id',
        'amount_result',
        'class_job',
        'class_job_level',
        'class_job_icon',
        'item_id',
    ];

    protected $casts = [
        'id' => 'integer',
        'item_id' => 'integer',
    ];

    /** @return HasMany<Ingredient> */
    public function ingredients(): HasMany
    {
        return $this->hasMany(Ingredient::class);
    }

    /** @return BelongsTo<Item, self> */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    // TODO: Remove
    public function alignAmounts(Server $server, float $target_amount): void
    {
        $ratio = $target_amount / $this->amount_result;
        $this->amount_result = $target_amount;

        /** @var CraftingCost */
        $craftingCost = $this->craftingCost($server);

        // TODO: Return data in a better format, instead of just assigning random values on the model that is not saved
        $this->purchase_cost = intval($craftingCost->purchase_cost * $target_amount);
        $this->market_craft_cost = intval($craftingCost->market_craft_cost * $ratio);
        $this->optimal_craft_cost = intval($craftingCost->optimal_craft_cost * $ratio);
        $this->item->market_price = $this->item->marketPrice($server)?->price ?: MarketPrice::DEFAULT_MARKET_PRICE;
        foreach ($this->ingredients as $ingredient) {
            $ingredient->amount = $ratio * $ingredient->amount;
            if ($ingredient->craftingRecipe !== null) {
                $ingredient->craftingRecipe->alignAmounts($server, $ingredient->amount);
            } else {
                $ingredient->item->market_price = $ingredient->item->marketPrice($server)?->price ?: MarketPrice::DEFAULT_MARKET_PRICE;
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

    /** @return HasMany<CraftingCost> */
    public function craftingCosts(): HasMany
    {
        return $this->hasMany(CraftingCost::class);
    }

    public function craftingCost(Server $server): ?CraftingCost
    {
        return $this->craftingCosts->first(fn (CraftingCost $craftingCost) => $craftingCost->server == $server);
    }

    // public function craftingCost(Server $server): ?CraftingCost
    // {
    //     return $this->hasOne(CraftingCost::class)->where('server', $server)->first();
    // }
}
