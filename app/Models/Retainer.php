<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 *
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $data_center
 * @property string $server
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Item> $items
 * @property-read int|null $items_count
 * @method static \Illuminate\Database\Eloquent\Builder|Retainer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Retainer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Retainer query()
 * @method static \Illuminate\Database\Eloquent\Builder|Retainer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Retainer whereDataCenter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Retainer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Retainer whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Retainer whereServer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Retainer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Retainer whereUserId($value)
 * @property-read \App\Models\User $user
 * @mixin \Eloquent
 */
class Retainer extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'data_center', 'server'];

    /** @return BelongsTo<User> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsToMany<Item> */
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class)->withTimestamps();
    }
}
