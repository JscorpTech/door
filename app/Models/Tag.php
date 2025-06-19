<?php

namespace App\Models;

use App\Models\Product;
use App\Traits\CacheManagerTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Class Tag
 *
 * @property int $id
 * @property string $tag
 * @property int $visit_count
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @package App\Models
 */
class Tag extends Model
{
    use HasFactory, CacheManagerTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tag',
        'visit_count',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'visit_count' => 'integer',
    ];

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->using(ProductTag::class);
    }

    protected static function boot(): void
    {
        parent::boot();
        static::saved(function ($model) {
            cacheRemoveByType(type: 'tags');
        });

        static::deleted(function ($model) {
            cacheRemoveByType(type: 'tags');
        });
    }
}
