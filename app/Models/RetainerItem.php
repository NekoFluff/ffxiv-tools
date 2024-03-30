<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property-read \App\Models\Item|null $item
 * @property-read \App\Models\Retainer|null $retainer
 * @method static \Illuminate\Database\Eloquent\Builder|RetainerItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RetainerItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RetainerItem query()
 * @mixin \Eloquent
 */
class RetainerItem extends Model
{
    use HasFactory;

    protected $fillable = ['retainer_id', 'item_id'];

    /** @return BelongsTo<Retainer> */
    public function retainer(): BelongsTo
    {
        return $this->belongsTo(Retainer::class);
    }

    /** @return BelongsTo<Item> */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
