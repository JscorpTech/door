<?php

namespace Modules\Blog\app\Models;

use App\Traits\StorageTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\Blog\Database\factories\BlogSeoFactory;

class BlogSeo extends Model
{
    use HasFactory, StorageTrait;

    protected $fillable = [
        'blog_id',
        'title',
        'description',
        'index',
        'no_follow',
        'no_image_index',
        'no_archive',
        'no_snippet',
        'max_snippet',
        'max_snippet_value',
        'max_video_preview',
        'max_video_preview_value',
        'max_image_preview',
        'max_image_preview_value',
        'image',
    ];

    protected $casts = [
        'blog_id' => 'integer',
        'title' => 'string',
        'description' => 'string',
        'index' => 'string',
        'no_follow' => 'string',
        'no_image_index' => 'string',
        'no_archive' => 'string',
        'no_snippet' => 'string',
        'max_snippet' => 'string',
        'max_snippet_value' => 'string',
        'max_video_preview' => 'string',
        'max_video_preview_value' => 'string',
        'max_image_preview' => 'string',
        'max_image_preview_value' => 'string',
        'image' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getImageFullUrlAttribute(): array
    {
        $value = $this->image;
        if (count($this->storage) > 0 ) {
            $storage = $this->storage->where('key','image')->first();
        }
        return $this->storageLink('blog/meta', $value, $storage['value'] ?? 'public');
    }

    public function updateOrInsert(array $params, array $data): bool
    {
        $blogSeo = $this->firstOrNew($params);
        $blogSeo->fill($data);
        $blogSeo->save();
        return true;
    }

    protected $appends = ['image_full_url'];
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
        });
    }
}
