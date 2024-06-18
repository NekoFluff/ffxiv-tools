<?php

namespace App\Models;

use App\Models\Enums\DataCenter;
use App\Models\Enums\Server;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 *
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property DataCenter $data_center
 * @property Server $server
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
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Listing> $listings
 * @property-read int|null $listings_count
 * @mixin \Eloquent
 */
class Retainer extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'data_center', 'server'];

    protected $casts = [
        'server' => Server::class,
        'data_center' => DataCenter::class,
    ];

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

    /**
     * Get the listings for the retainer.
     */
    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class, 'retainer_name', 'name')->orderBy('price_per_unit', 'asc');
    }
}
