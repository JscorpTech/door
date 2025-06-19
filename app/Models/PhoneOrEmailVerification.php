<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserToken
 *
 * @property int $id
 * @property string|null $phone_or_email
 * @property string|null $token
 * @property int $otp_hit_count
 * @property bool $is_temp_blocked
 * @property Carbon|null $temp_block_time
 * @property Carbon|null $expires_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class PhoneOrEmailVerification extends Model
{
    protected $fillable = [
        'phone_or_email',
        'token',
        'otp_hit_count',
        'is_temp_blocked',
        'temp_block_time',
        'expires_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'phone_or_email' => 'string',
        'token' => 'string',
        'otp_hit_count' => 'integer',
        'is_temp_blocked' => 'integer',
        'temp_block_time' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
