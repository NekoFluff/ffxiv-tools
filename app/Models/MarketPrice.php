<?php

namespace App\Models;

use App\Models\Enums\DataCenter;
use App\Models\Enums\Server;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $item_id
 * @property DataCenter $data_center
 * @property Server $server
 * @property int $price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Item $item
 *
 * @method static \Illuminate\Database\Eloquent\Builder|MarketPrice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MarketPrice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MarketPrice query()
 * @method static \Illuminate\Database\Eloquent\Builder|MarketPrice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketPrice whereDataCenter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketPrice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketPrice whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketPrice wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketPrice whereServer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketPrice whereUpdatedAt($value)
 * @method static \Database\Factories\MarketPriceFactory factory($count = null, $state = [])
 *
 * @mixin \Eloquent
 */
class MarketPrice extends Model
{
    use HasFactory;

    public const DEFAULT_MARKET_PRICE = 10000000;

    protected $fillable = [
        'item_id',
        'data_center',
        'server',
        'price',
    ];

    protected $casts = [
        'server' => Server::class,
        'data_center' => DataCenter::class,
    ];

    /** @return BelongsTo<Item, self> */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
