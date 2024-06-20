<?php

namespace App\Models;

use App\Models\Enums\Server;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\Item
 *
 * @property int $id
 * @property string $name
 * @property string $icon
 * @property int $vendor_price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Ingredient> $ingredients
 * @property-read int|null $ingredients_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Listing> $listings
 * @property-read int|null $listings_count
 * @property-read \App\Models\Recipe|null $recipe
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Sale> $sales
 * @property-read int|null $sales_count
 *
 * @method static \Database\Factories\ItemFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Item newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Item newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Item query()
 * @method static \Illuminate\Database\Eloquent\Builder|Item whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Item whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Item whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Item whereMarketPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Item whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Item whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Item whereVendorPrice($value)
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MarketPrice> $marketPrices
 * @property-read int|null $market_prices_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Retainer> $retainers
 * @property-read int|null $retainers_count
 *
 * @mixin \Eloquent
 */
class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'icon',
        'vendor_price',
    ];

    protected $attributes = [
        'vendor_price' => 0,
    ];

    protected $casts = [
        'id' => 'integer',
        'vendor_price' => 'integer',
    ];

    /** @return HasMany<Ingredient> */
    public function ingredients(): HasMany
    {
        return $this->hasMany(Ingredient::class);
    }

    /** @return HasMany<Sale> */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /** @return HasMany<Listing> */
    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    /** @return HasOne<Recipe> */
    public function recipe(): HasOne
    {
        return $this->hasOne(Recipe::class);
    }

    /** @return HasMany<MarketPrice> */
    public function marketPrices(): HasMany
    {
        return $this->hasMany(MarketPrice::class);
    }

    /** @return ?MarketPrice */
    public function marketPrice(Server $server): ?MarketPrice
    {
        return $this->marketPrices->filter(fn (MarketPrice $marketPrice) => $marketPrice->server === $server)->first();
    }

    /** @return BelongsToMany<Retainer> */
    public function retainers(): BelongsToMany
    {
        return $this->belongsToMany(Retainer::class)->withTimestamps();
    }
}
