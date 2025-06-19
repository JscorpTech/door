<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\RestockProductCustomer
 *
 * @property int $id
 * @property int $restock_product_id
 * @property string|null $customer_id
 * @property string|null $variant
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class RestockProductCustomer extends Model
{
    use HasFactory;

    protected $table = 'restock_product_customers';

    protected $fillable = [
        'restock_product_id',
        'customer_id',
        'variant',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'restock_product_id' => 'integer',
        'customer_id' => 'integer',
        'variant' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
