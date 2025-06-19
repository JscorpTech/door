<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class LoginSetup
 *
 * @property int $id
 * @property string|null $key
 * @property string|null $value
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class LoginSetup extends Model
{
    use HasFactory;

    protected $table = 'login_setups';

    protected $fillable = [
        'key',
        'value',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'key' => 'string',
        'value' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::saved(function ($model) {
            cacheRemoveByType(type: 'login_setups');
        });

        static::deleted(function ($model) {
            cacheRemoveByType(type: 'login_setups');
        });
    }
}
