<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\StockClearanceSetup
 *
 * @property int $id
 * @property string $setup_by
 * @property int|null $user_id
 * @property int|null $shop_id
 * @property bool $is_active
 * @property string $discount_type
 * @property float $discount_amount
 * @property string|null $offer_active_time
 * @property string|null $offer_active_range_start
 * @property string|null $offer_active_range_end
 * @property bool $show_in_homepage
 * @property bool $show_in_homepage_once
 * @property bool $show_in_shop
 * @property Carbon|null $duration_start_date
 * @property Carbon|null $duration_end_date
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 */
class StockClearanceSetup extends Model
{
    use HasFactory;

    protected $fillable = [
        'setup_by',
        'user_id',
        'shop_id',
        'is_active',
        'discount_type',
        'discount_amount',
        'offer_active_time',
        'offer_active_range_start',
        'offer_active_range_end',
        'show_in_homepage',
        'show_in_homepage_once',
        'show_in_shop',
        'duration_start_date',
        'duration_end_date',
    ];

    protected $casts = [
        'setup_by' => 'string',
        'user_id' => 'integer',
        'shop_id' => 'integer',
        'is_active' => 'integer',
        'discount_type' => 'string',
        'discount_amount' => 'float',
        'offer_active_time' => 'string',
        'offer_active_range_start' => 'datetime:h:i:s A',
        'offer_active_range_end' => 'datetime:h:i:s A',
        'show_in_homepage' => 'integer',
        'show_in_homepage_once' => 'integer',
        'show_in_shop' => 'integer',
        'duration_start_date' => 'datetime',
        'duration_end_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'shop_id', 'id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(StockClearanceProduct::class, 'shop_id', 'shop_id');
    }

    public function seller(): HasMany
    {
        return $this->hasMany(Product::class, 'user_id','user_id')->where(['added_by' => 'seller']);
    }
}
