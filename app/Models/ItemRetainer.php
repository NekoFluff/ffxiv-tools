<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Database\Factories\ItemRetainerFactory;

/**
 * @property-read \App\Models\Item|null $item
 * @property-read \App\Models\Retainer|null $retainer
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ItemRetainer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ItemRetainer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ItemRetainer query()
 *
 * @mixin \Eloquent
 */
class ItemRetainer extends Model
{
    /** @use HasFactory<ItemRetainerFactory> */
    use HasFactory;

    protected $fillable = ['retainer_id', 'item_id'];

    /** @return BelongsTo<Retainer, self> */
    public function retainer(): BelongsTo
    {
        return $this->belongsTo(Retainer::class);
    }

    /** @return BelongsTo<Item, self> */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
