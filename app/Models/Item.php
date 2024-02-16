<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Item extends Model
{
    use HasFactory;

    public const DEFAULT_MARKET_PRICE = 10000000;

    protected $fillable = [
        'id',
        'name',
        'icon',
        'market_price',
        'vendor_price',
    ];

    protected $casts = [
        'id' => 'integer',
        'market_price' => 'integer',
        'vendor_price' => 'integer',
    ];

    /** @return HasMany<Ingredient> */
    public function ingredients(): HasMany
    {
        return $this->hasMany(Ingredient::class);
    }

    /** @return HasOne<Recipe> */
    public function recipe(): HasOne
    {
        return $this->hasOne(Recipe::class);
    }
}
