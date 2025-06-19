<?php

namespace App\Models;

use App\Traits\CacheManagerTrait;
use Illuminate\Database\Eloquent\Model;

class BusinessSetting extends Model
{
    use CacheManagerTrait;

    protected $fillable = ['type', 'value', 'created_at', 'updated_at'];

    protected $casts = [
        'id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::saved(function ($model) {
            cacheRemoveByType(type: 'business_settings');
        });

        static::deleted(function ($model) {
            cacheRemoveByType(type: 'business_settings');
        });
    }
}
