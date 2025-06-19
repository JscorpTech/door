<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class DigitalProductPublishingHouse
 *
 * @package App\Models
 * @property int $id
 * @property int $publishing_house_id
 * @property int|null $product_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class DigitalProductPublishingHouse extends Model
{
    use HasFactory;

    protected $table = 'digital_product_publishing_houses';

    protected $fillable = [
        'publishing_house_id',
        'product_id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'publishing_house_id' => 'integer',
        'product_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function publishingHouse(): BelongsTo
    {
        return $this->belongsTo(PublishingHouse::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

}
