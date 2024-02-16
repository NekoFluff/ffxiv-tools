<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    /** @return BelongsTo<Item> */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
