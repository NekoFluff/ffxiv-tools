<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Listing
 *
 * @property int $id
 * @property int $item_id
 * @property string $retainer_name
 * @property int $retainer_city
 * @property int $quantity
 * @property bool $hq
 * @property int $price_per_unit
 * @property int $total
 * @property int $tax
 * @property \Illuminate\Support\Carbon $last_review_time
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Item $item
 *
 * @method static \Database\Factories\ListingFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Listing newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Listing newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Listing query()
 * @method static \Illuminate\Database\Eloquent\Builder|Listing whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Listing whereHq($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Listing whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Listing whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Listing whereLastReviewTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Listing wherePricePerUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Listing whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Listing whereRetainerCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Listing whereRetainerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Listing whereTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Listing whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Listing whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Listing extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'retainer_name',
        'retainer_city',
        'quantity',
        'hq',
        'price_per_unit',
        'total',
        'tax',
        'last_review_time',
    ];

    protected $casts = [
        'item_id' => 'integer',
        'retainer_city' => 'integer',
        'quantity' => 'integer',
        'hq' => 'boolean',
        'price_per_unit' => 'integer',
        'total' => 'integer',
        'tax' => 'integer',
        'last_review_time' => 'datetime',
    ];

    /** @return BelongsTo<Item, Listing> */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
