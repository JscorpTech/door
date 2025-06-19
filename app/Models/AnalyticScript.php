<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AnalyticScript
 *
 * @property int $id
 * @property string $name
 * @property string|null $type
 * @property string|null $script_id
 * @property string|null $script
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class AnalyticScript extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'script_id',
        'script',
        'is_active',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'name' => 'string',
        'type' => 'string',
        'script_id' => 'string',
        'script' => 'string',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::saved(function ($model) {
            cacheRemoveByType(type: 'analytic_script');
        });

        static::deleted(function ($model) {
            cacheRemoveByType(type: 'analytic_script');
        });
    }
}
