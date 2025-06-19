<?php

namespace Modules\Blog\app\Http\Controllers\Admin;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Services\ProductService;
use App\Traits\FileManagerTrait;
use App\Traits\SettingsTrait;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Modules\Blog\app\Http\Requests\BlogAddRequest;
use Modules\Blog\app\Http\Requests\BlogUpdateRequest;
use Modules\Blog\app\Models\BlogSeo;
use Modules\Blog\app\Services\Frontend\FrontendBlogService;
use Modules\Blog\app\Traits\BlogTrait;
use Modules\Blog\app\Traits\BlogTranslationTrait;
use Modules\Blog\app\Models\BlogCategory;
use Modules\Blog\app\Models\Blog;
use Illuminate\Support\Str;
use App\Models\Storage as StorageModel;

class BlogController extends Controller
{
    use SettingsTrait, BlogTranslationTrait, BlogTrait;

    use FileManagerTrait {
        delete as deleteFile;
        update as updateFile;
        upload as uploadFile;
    }

    public function __construct(
        private readonly BlogCategory                       $blogCategory,
        private readonly Blog                               $blog,
        private readonly BlogSeo                            $blogSeo,
        private readonly StorageModel                       $storageModel,
        private readonly BusinessSettingRepositoryInterface $businessSettingRepo,
        private readonly FrontendBlogService                $frontendBlogService,
    )
    {
    }

    public function index(Request|null $request, string $type = null): View
    {
        return $this->getListView($request);
    }

    public function getListView(Request $request): View
    {
        $filters = [
            'category_id' => $request['category_id'],
            'publish_date' => $request['publish_date'],
        ];

        $categories = $this->blogCategory->with('translations')->get();
        $searchValue = $request['searchValue'];
        $languages = getWebConfig(name: 'pnc_language') ?? null;
        $defaultLanguage = $languages[0];

        $titleData = getWebConfig(name: 'blog_feature_title') ?? [];
        $subTitleData = getWebConfig(name: 'blog_feature_sub_title') ?? [];

        $blogId = 100000 + $this->blogCategory->all()->count() + 1;
        if ($this->blogCategory->find($blogId)) {
            $blogId = $this->blogCategory->orderBy('id', 'DESC')->first()->id + 1;
        }

        $blogs = $this->blog
            ->with('category')
            ->when($searchValue, function ($query) use ($searchValue) {
                $query->where('title', 'like', "%{$searchValue}%")
                    ->orWhereHas('category', function ($query) use ($searchValue) {
                        return $query->where('name', 'like', "%{$searchValue}%");
                    });
            })->when(!empty($filters['publish_date']), function ($query) use ($filters) {
                $dates = explode(' - ', $filters['publish_date']);
                $startDate = Carbon::createFromFormat('m/d/Y', $dates[0])->startOfDay();
                $endDate = Carbon::createFromFormat('m/d/Y', $dates[1])->endOfDay();
                $query->whereBetween('publish_date', [$startDate, $endDate]);
            })->when(!empty($filters['category_id']) && $filters['category_id'] != 'all', function ($query) use ($filters) {
                return $query->where(['category_id' => $filters['category_id']]);
            })->paginate(getWebConfig(name: 'pagination_limit'));

        return view('blog::admin-views.blog.index', compact('blogs', 'categories', 'searchValue', 'titleData', 'subTitleData', 'languages', 'defaultLanguage', 'blogId'));
    }

    public function updateIntro(Request $request): RedirectResponse
    {
        $this->businessSettingRepo->updateOrInsert(type: 'blog_feature_title', value: json_encode($request['title'] ?? []));
        $this->businessSettingRepo->updateOrInsert(type: 'blog_feature_sub_title', value: json_encode($request['sub_title'] ?? []));
        Toastr::success(translate('updated_successfully'));
        return back();
    }

    public function getAddView(): View
    {
        $categories = $this->blogCategory->withoutGlobalScopes()
            ->with('translations')
            ->orderBy('updated_at', 'desc')
            ->paginate(getWebConfig(name: 'pagination_limit'));
        $languages = getWebConfig(name: 'pnc_language') ?? null;
        $defaultLanguage = $languages[0];
        return view('blog::admin-views.blog.create', compact('categories', 'languages', 'defaultLanguage'));
    }

    public function addBlog(BlogAddRequest $request): JsonResponse
    {
        $imagePath = $request['image'] ? $this->upload(dir: 'blog/image/', format: 'webp', image: $request['image']) : null;
        $storage = config('filesystems.disks.default') ?? 'public';
        $data = [
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

        $savedBlog = $this->blog->create($data);
        $this->addBlogTranslation(request: $request, id: $savedBlog->id);
        $this->blogSeo->create($this->getBlogSeoData(request: $request, blog: $savedBlog, action: 'add'));

        return response()->json([
            'status' => 1,
            'redirect' => route('admin.blog.view'),
            'message' => $request['is_draft'] ? translate('Blog_drafted_successfully') : translate('Blog_published_successfully'),
        ]);
    }

    private function getBlogSeoData(object $request, object|null $blog, $action = null): array
    {
        if ($blog) {
            if ($request->file('meta_image')) {
                $metaImage = $this->updateFile(dir: 'blog/meta/', oldImage: $blog?->seoInfo?->image, format: 'png', image: $request['meta_image']);
            } elseif (!$request->file('meta_image') && $request->file('image') && $action == 'add') {
                $metaImage = $this->updateFile(dir: 'blog/meta/', oldImage: $blog?->seoInfo?->image, format: 'webp', image: $request['image']);
            } else {
                $metaImage = $blog?->seoInfo?->image;
            }
        } else {
            if ($request->file('meta_image')) {
                $metaImage = $this->uploadFile(dir: 'blog/meta/', format: 'webp', image: $request['meta_image']);
            } elseif (!$request->file('meta_image') && $request->file('image') && $action == 'add') {
                $metaImage = $this->uploadFile(dir: 'blog/meta/', format: 'webp', image: $request['image']);
            }
        }
        return [
            "blog_id" => $blog['id'],
            "title" => $request['meta_title'] ?? ($blog ? $blog?->seoInfo?->title : null),
            "description" => $request['meta_description'] ?? ($blog ? $blog?->seoInfo?->description : null),
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
            "image" => $metaImage ?? ($blog ? $blog?->seoInfo?->image : null),
            "created_at" => now(),
            "updated_at" => now(),
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

    public function getUpdateView(Request $request): View
    {
        $languages = getWebConfig(name: 'pnc_language') ?? null;
        $defaultLanguage = $languages[0];
        $blog = $this->blog->withoutGlobalScopes()->where('id', $request['id'])->with('translations', 'seoInfo')->first();
        $categories = $this->blogCategory->withoutGlobalScopes()
            ->with('translations')
            ->orderBy('updated_at', 'desc')
            ->paginate(getWebConfig(name: 'pagination_limit'));
        return view('blog::admin-views.blog.edit', compact('categories', 'blog', 'languages', 'defaultLanguage'));
    }

    public function update(BlogUpdateRequest $request)
    {
        $blog = $this->blog->withoutGlobalScopes()
            ->with('translations', 'seoInfo')
            ->where('id', $request->id)
            ->first();
        if (!$blog) {
            return response()->json(['status' => 0, 'message' => translate('Blog not found')]);
        }

        if ($request->clear_draft == 1) {
            return $this->clearDraft($blog);
        }
        $storage = config('filesystems.disks.default', 'public');
        $data = $request->is_draft
            ? $this->prepareDraftData($request, $blog, $storage)
            : $this->preparePublishedData($request, $blog, $storage);
        $blog->update($data);
        $this->updateBlogTranslation(request: $request, id: $blog->id);
        if (($request->is_draft && !$blog) || (!$request->is_draft && $blog)) {
            if ($request->has('meta_title') || $request->has('meta_description')) {
                $this->blogSeo->updateOrInsert(
                    params: ['blog_id' => $blog->id],
                    data: $this->getBlogSeoData(request: $request, blog: $blog, action: 'add')
                );
            }
        }

        return response()->json([
            'status' => 1,
            'redirect' => route('admin.blog.view'),
            'message' => $request->is_draft
                ? translate('Blog drafted successfully')
                : translate('Blog published successfully'),
        ]);
    }

    private function clearDraft($blog)
    {
        if ($blog->is_published) {
            if ($blog->draft_image != $blog->image) {
                $this->deleteFile('blog/image/' . $blog->draft_image);
            }

            $blog->update([
                'is_draft' => 0,
                'draft_data' => json_encode([]),
                'draft_image' => '',
            ]);

        } else {
            if ($blog->draft_image != '') {
                $this->deleteFile('blog/image/' . $blog->draft_image);
            }
            if ($blog?->seoInfo?->image != '') {
                $this->deleteFile('blog/meta/' . $blog?->seoInfo?->image);
            }
            $blog->delete();
            $blog->seoInfo->delete();
        }

        return response()->json([
            'status' => 1,
            'redirect' => route('admin.blog.view'),
            'message' => translate('Blog draft cleared successfully'),
        ]);
    }

    private function prepareDraftData($request, $blog, $storage)
    {
        $imagePath = $request->has('image')
            ? $this->updateFile(dir: 'blog/image/', oldImage: $blog->draft_image != $blog?->image ? $blog->draft_image : null, format: 'webp', image: $request->image)
            : $blog->image ?? $blog->draft_image;

        return [
            'title' => $blog->title,
            'slug' => $blog->slug,
            'description' => $blog->description,
            'category_id' => $blog->category_id,
            'writer' => $blog->writer,
            'publish_date' => $blog->publish_date,
            'is_draft' => 1,
            'draft_data' => $this->getDraftData($request),
            'draft_image' => $imagePath,
            'draft_image_storage_type' => $request->has('image') ? $storage : $blog->draft_image_storage_type,
        ];
    }

    private function preparePublishedData($request, $blog, $storage)
    {
        if ($request->has('image')) {
            $imagePath = $this->updateFile('blog/image/', $blog->image, 'webp', $request->image);
        } else if ($request->page == 'edit') {
            $imagePath = $blog->image;
            if ($blog->image != $blog->draft_image) {
                $this->deleteFile('blog/image/' . $blog->draft_image);
            }
        } else {
            $imagePath = $blog->draft_image;
            if ($blog->image != $blog->draft_image) {
                $this->deleteFile('blog/image/' . $blog->image);
            }
        }

        if ($blog->is_draft) {
            $this->storageModel->where([
                'data_type' => 'Modules\Blog\app\Models\Blog',
                'data_id' => $blog->id,
                'key' => 'draft_image',
            ])->update(['key' => 'image']);
        }

        return [
            'title' => $request->input('title.en'),
            'slug' => $request->input('title.en') != $blog->title ? $this->getSlug($request) : $blog->slug,
            'description' => $request->input('description.en'),
            'category_id' => $request->blog_category,
            'writer' => $request->writer,
            'publish_date' => $request->publish_date ?? now(),
            'image' => $imagePath,
            'image_storage_type' => $request->has('image') ? $storage : $blog->image_storage_type,
            'is_draft' => 0,
            'is_published' => 1,
            'draft_data' => json_encode([]),
            'draft_image' => '',
            'draft_image_storage_type' => '',
            'status' => 1,
        ];
    }


    public function updateStatus(Request $request): JsonResponse
    {
        $this->businessSettingRepo->updateOrInsert(type: 'blog_feature_active_status', value: $request['status'] ?? 0);
        return response()->json([
            'status' => true,
            'message' => translate('Status_updated_successfully')
        ]);
    }

    public function updateBlogStatus(Request $request, $id): JsonResponse
    {
        $blog = $this->blog->find($id);
        $blog->update(['status' => $request->status ?? 0]);

        return response()->json([
            'message' => translate('Status update successfully')
        ]);
    }

    public function draftEdit($blogId): View
    {
        $languages = getWebConfig(name: 'pnc_language') ?? null;
        $defaultLanguage = $languages[0];
        $blog = $this->blog->withoutGlobalScopes()->where('id', $blogId)->with('translations')->first();
        $categories = $this->blogCategory->withoutGlobalScopes()
            ->with('translations')
            ->orderBy('updated_at', 'desc')
            ->paginate(getWebConfig(name: 'pagination_limit'));
        return view('blog::admin-views.blog.draft-edit', compact('blog', 'categories', 'languages', 'defaultLanguage'));
    }

    public function delete(Request $request): RedirectResponse
    {
        $blog = $this->blog->find($request->id);
        if (!$blog) {
            Toastr::error(translate('Blog not found.'));
            return back();
        }
        $this->deleteFile(filePath: 'blog/image/' . $blog['image']);
        $blog->delete();

        Toastr::success(translate('blog_deleted_successfully'));
        return redirect()->back();
    }

    public function getSlug(object $request): string
    {
        return Str::slug($request['title'][array_search('en', $request['lang'])], '-') . '-' . Str::random(6);
    }
}
