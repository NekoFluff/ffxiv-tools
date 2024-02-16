<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    /** @return BelongsTo<Item> */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
