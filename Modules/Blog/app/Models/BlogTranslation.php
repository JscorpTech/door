<?php

namespace Modules\Blog\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BlogTranslation extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'blog_translations';

    protected $fillable = [
        'translation_type',
        'translation_id',
        'locale',
        'key',
        'value',
        'is_draft'
    ];

    protected $casts = [
        'translation_id' => 'integer',
        'translation_type' => 'string',
        'locale' => 'string',
        'key' => 'string',
        'value' => 'string',
        'id' => 'integer',
        'is_draft' => 'integer'
    ];

    public function translation(): MorphTo
    {
        return $this->morphTo();
    }

    protected static function boot(): void
    {
        parent::boot();
    }
}
