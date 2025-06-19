<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\RestockProduct
 *
 * @property int $id
 * @property int $product_id
 * @property string|null $variant
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class RestockProduct extends Model
{
    use HasFactory;

    protected $table = 'restock_products';

    protected $fillable = [
        'product_id',
        'variant',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'product_id' => 'integer',
        'variant' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function restockProductCustomers(): HasMany
    {
        return $this->hasMany(RestockProductCustomer::class, 'restock_product_id');
    }
}
