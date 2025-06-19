<?php

namespace Modules\Blog\app\Models;

use App\Traits\StorageTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class Blog extends Model
{
    use HasFactory, StorageTrait;

    protected $fillable = [
        'slug',
        'readable_id',
        'category_id',
        'writer',
        'title',
        'description',
        'image',
        'image_storage_type',
        'draft_image',
        'draft_image_storage_type',
        'publish_date',
        'is_published',
        'status',
        'is_draft',
        'draft_data',
        'click_count',
    ];

    protected $casts = [
        'category_id' => 'integer',
        'publish_date' => 'datetime',
        'is_published' => 'boolean',
        'status' => 'boolean',
        'is_draft' => 'boolean',
        'draft_data' => 'array',
        'click_count' => 'integer',
        'image' => 'string',
        'image_storage_type' => 'string',
        'draft_image' => 'string',
        'draft_image_storage_type' => 'string',
    ];

    protected $appends = ['thumbnail_full_url', 'draft_thumbnail_full_url'];

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function seoInfo(): BelongsTo
    {
        return $this->belongsTo(BlogSeo::class, 'id', 'blog_id');
    }

    public function translations(): MorphMany
    {
        return $this->morphMany('Modules\Blog\app\Models\BlogTranslation', 'translation');
    }

    public function getThumbnailFullUrlAttribute(): string|null|array
    {
        $value = $this->image;
        return $this->storageLink('blog/image', $value, $this->image_storage_type ?? 'public');
    }

    public function getDraftThumbnailFullUrlAttribute(): string|null|array
    {
        $value = $this->draft_image;
        return $this->storageLink('blog/image', $value, $this->draft_image_storage_type ?? 'public');
    }

    public function getTitleAttribute($title): string|null
    {
        if (strpos(url()->current(), '/admin') || strpos(url()->current(), '/vendor') || strpos(url()->current(), '/seller')) {
            return $title;
        }
        $translation = $this->translations()->where(['key' => 'title', 'is_draft' => 0])
            ->when(strpos(url()->current(), '/api'), function ($query) {
                return $query->where('locale', App::getLocale());
            })
            ->when(!strpos(url()->current(), '/api'), function ($query) {
                return $query->where('locale', getDefaultLanguage());
            })
            ->first();

        return $translation?->value ?? $title;
    }

    public function getDescriptionAttribute($description): string|null
    {
        if (strpos(url()->current(), '/admin') || strpos(url()->current(), '/vendor') || strpos(url()->current(), '/seller')) {
            return $description;
        }
        $translation = $this->translations()->where(['key' => 'description', 'is_draft' => 0])
            ->when(strpos(url()->current(), '/api'), function ($query) {
                return $query->where('locale', App::getLocale());
            })
            ->when(!strpos(url()->current(), '/api'), function ($query) {
                return $query->where('locale', getDefaultLanguage());
            })
            ->first();
        return $translation?->value ?? $description;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class);
    }

    protected static function boot(): void
    {
        parent::boot();
        static::saved(function ($model) {
            if ($model->isDirty('image')) {
                $storage = config('filesystems.disks.default') ?? 'public';
                DB::table('storages')->updateOrInsert([
                    'data_type' => get_class($model),
                    'data_id' => $model->id,
                    'key' => 'image',
                ], [
                    'value' => $storage,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if ($model->isDirty('draft_image')) {
                $storage = config('filesystems.disks.default') ?? 'public';
                DB::table('storages')->updateOrInsert([
                    'data_type' => get_class($model),
                    'data_id' => $model->id,
                    'key' => 'draft_image',
                ], [
                    'value' => $storage,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        static::addGlobalScope('translate', function (Builder $builder) {
            $builder->with(['translations' => function ($query) {
                if (strpos(url()->current(), '/api')) {
                    return $query->where('locale', App::getLocale());
                } else {
                    return $query->where('locale', getDefaultLanguage());
                }
            }]);
        });
    }
}
