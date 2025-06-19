<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Author
 *
 * @package App\Models
 * @property int $id
 * @property string $name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class PublishingHouse extends Model
{
    use HasFactory;

    protected $table = 'publishing_houses';

    protected $fillable = [
        'name',
        'created_at',
        'updated_at',
    ];


    protected $casts = [
        'name' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function publishingHouseProducts(): HasMany
    {
        return $this->hasMany(DigitalProductPublishingHouse::class, 'publishing_house_id');
    }

}
