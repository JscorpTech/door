<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class DigitalProductAuthor
 *
 * @package App\Models
 * @property int $id
 * @property int $author_id
 * @property int|null $product_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class DigitalProductAuthor extends Model
{
    use HasFactory;

    protected $table = 'digital_product_authors';

    protected $fillable = [
        'author_id',
        'product_id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'author_id' => 'integer',
        'product_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
