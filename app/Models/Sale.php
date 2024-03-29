<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Sale
 *
 * @property int $id
 * @property int $item_id
 * @property int $quantity
 * @property int $price_per_unit
 * @property string $buyer_name
 * @property \Illuminate\Support\Carbon $timestamp
 * @property bool $hq
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Item $item
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Sale newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Sale newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Sale query()
 * @method static \Illuminate\Database\Eloquent\Builder|Sale whereBuyerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sale whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sale whereHq($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sale whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sale whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sale wherePricePerUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sale whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sale whereTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sale whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'quantity',
        'price_per_unit',
        'buyer_name',
        'timestamp',
        'hq',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'price_per_unit' => 'integer',
        'quantity' => 'integer',
        'hq' => 'boolean',
    ];

    /** @return BelongsTo<Item, Sale> */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
