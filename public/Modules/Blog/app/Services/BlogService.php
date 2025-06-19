<?php

namespace Modules\Blog\app\Services;

use App\Traits\FileManagerTrait;
use Illuminate\Support\Str;
use Modules\Blog\app\Traits\BlogTrait;

class BlogService
{
    use FileManagerTrait, BlogTrait;

    public function getSlug(object $request): string
    {
        return Str::slug($request['title'][array_search('en', $request['lang'])], '-') . '-' . Str::random(6);
    }

    public function getAddData(object|array $request): array
    {
        $imagePath = $request['image'] ? $this->upload(dir: 'blog/image/', format: 'webp', image: $request['image']) : null;
        $storage = config('filesystems.disks.default') ?? 'public';

        return [
            'title' => $request['title']['en'],
            'slug' => $this->getSlug($request),
            'readable_id' => $this->getBlogReadableId(),
            "description" => $request['description']['en'] ?? "",
            "category_id" => $request['blog_category'],
            "writer" => $request['writer'],
            "publish_date" => $request['publish_date'] ?? now(),
            'status' => $request['status'] ?? 1,
            'is_draft' => $request['is_draft'] ?? 0,
            'click_count' => 0,
            'is_published' => $request['is_draft'] ? 0 : 1,
            'image' => !$request['is_draft'] ? $imagePath : '',
            'image_storage_type' => !$request['is_draft'] && $request->has('image') ? $storage : null,
            'draft_image' => $request['is_draft'] ? $imagePath : '',
            'draft_image_storage_type' => $request['is_draft'] && $request->has('image') ? $storage : null,
            "draft_data" => $this->getDraftData(request: $request),
        ];
    }

    public function getDraftData(object|array $request): bool|string
    {
        return json_encode($request['is_draft'] ? [
            'title' => $request['title']['en'],
            "description" => $request['description']['en'],
            "category_id" => $request['blog_category'],
            "writer" => $request['writer'],
            "publish_date" => $request['publish_date'] ?? now(),
        ] : []);
    }

    public function getUpdateData(object|array $request): array
    {
        return [
            'name' => $request['name']['en'],
            'slug' => $this->getCategorySlug(request: $request),
        ];
    }

    public function getBlogSeoData(object $request, object|null $blog, $action = null): array
    {
        if ($blog) {
            if ($request->file('meta_image')) {
                $metaImage = $this->update(dir: 'product/meta/', oldImage: $blog['meta_image'], format: 'png', image: $request['meta_image']);
            } elseif (!$request->file('meta_image') && $request->file('image') && $action == 'add') {
                $metaImage = $this->upload(dir: 'product/meta/', format: 'webp', image: $request['image']);
            } else {
                $metaImage = $blog?->seoInfo?->image ?? $blog['meta_image'];
            }
        } else {
            if ($request->file('meta_image')) {
                $metaImage = $this->upload(dir: 'product/meta/', format: 'webp', image: $request['meta_image']);
            } elseif (!$request->file('meta_image') && $request->file('image') && $action == 'add') {
                $metaImage = $this->upload(dir: 'product/meta/', format: 'webp', image: $request['image']);
            }
        }

        return [
            "blog_id" => $blog['id'],
            "title" => $request['meta_title'] ?? ($blog ? $blog['meta_title'] : null),
            "description" => $request['meta_description'] ?? ($blog ? $blog['meta_description'] : null),
            "index" => $request['meta_index'] == 'index' ? '' : 'noindex',
            "no_follow" => $request['meta_no_follow'] ? 'nofollow' : '',
            "no_image_index" => $request['meta_no_image_index'] ? 'noimageindex' : '',
            "no_archive" => $request['meta_no_archive'] ? 'noarchive' : '',
            "no_snippet" => $request['meta_no_snippet'] ?? 0,
            "max_snippet" => $request['meta_max_snippet'] ?? 0,
            "max_snippet_value" => $request['meta_max_snippet_value'] ?? 0,
            "max_video_preview" => $request['meta_max_video_preview'] ?? 0,
            "max_video_preview_value" => $request['meta_max_video_preview_value'] ?? 0,
            "max_image_preview" => $request['meta_max_image_preview'] ?? 0,
            "max_image_preview_value" => $request['meta_max_image_preview_value'] ?? 0,
            "image" => $metaImage ?? ($blog ? $blog['meta_image'] : null),
            "created_at" => now(),
            "updated_at" => now(),
        ];
    }
}
