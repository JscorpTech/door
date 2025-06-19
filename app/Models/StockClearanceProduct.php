<?php

namespace App\Models;

use App\Traits\CacheManagerTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\StockClearanceProduct
 *
 * @property int $id
 * @property string $added_by
 * @property int|null $user_id
 * @property bool $is_active
 * @property string $discount_type
 * @property float $discount_amount
 * @property int|null $product_id
 * @property int|null $shop_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 */
class StockClearanceProduct extends Model
{
    use HasFactory;
    use CacheManagerTrait;

    protected $fillable = [
        'added_by',
        'user_id',
        'is_active',
        'discount_type',
        'discount_amount',
        'product_id',
        'setup_id',
        'shop_id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'added_by' => 'string',
        'user_id' => 'integer',
        'is_active' => 'integer',
        'discount_type' => 'string',
        'discount_amount' => 'float',
        'product_id' => 'integer',
        'shop_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query
            ->where(['is_active' => 1])
            ->whereIn('added_by', ['admin', 'vendor'])
        ->CheckConfig();
    }

    public function scopeCheckConfig($query): void
    {
        $currentTime = Carbon::now()->format('H:i:s');
        $query->whereHas('setup', function ($query) use ($currentTime) {
            return $query->where('is_active', 1)
                ->whereDate('duration_start_date', '<=', Carbon::now())
                ->whereDate('duration_end_date', '>=', Carbon::now())
                ->where(function ($subQuery) use ($currentTime) {
                    return $subQuery->where(function ($query) use ($currentTime) {
                            return $query->where('offer_active_time', 'specific_time')
                                ->whereTime('offer_active_range_start', '<=', $currentTime)
                                ->whereTime('offer_active_range_end', '>=', $currentTime);
                        })->orWhere(function ($query) {
                            return $query->where('offer_active_time', 'always')
                                ->whereNull('offer_active_range_start')
                                ->whereNull('offer_active_range_end');
                        });
                });
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'user_id');
    }

    public function setup(): BelongsTo
    {
        return $this->belongsTo(StockClearanceSetup::class, 'setup_id');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::saved(function ($model) {
            cacheRemoveByType(type: 'products');
        });

        static::deleted(function ($model) {
            cacheRemoveByType(type: 'products');
        });
    }
}
