<?php

namespace Modules\Blog\app\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Request;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Modules\Blog\app\Models\Blog;
use Modules\Blog\app\Models\BlogCategory;
use DOMDocument;
use Modules\Blog\app\Models\BlogTranslation;
use Modules\Blog\app\Services\Frontend\FrontendBlogService;
use Modules\Blog\app\Traits\BlogCategoryTrait;
use Modules\Blog\app\Traits\BlogTrait;

class FrontendBlogController extends Controller
{

    use BlogTrait;
    use BlogCategoryTrait;

    public function __construct(
        private readonly FrontendBlogService $frontendBlogService,
        private readonly Blog                $blog,
        private readonly BlogTranslation     $blogTranslation,
        private readonly BlogCategory        $blogCategory
    )
    {
    }

    public function index(Request $request): View
    {
        $this->frontendBlogService->getCheckLocale(request: $request);
        $titleData = getWebConfig(name: 'blog_feature_title') ?? [];
        $subTitleData = getWebConfig(name: 'blog_feature_sub_title') ?? [];

        $search = $request->input('search');
        $blogListQuery = Blog::active()
            ->with(['category' => function ($query) {
                return $query->active();
            }, 'translations'])
            ->when($search, function ($query) use ($search) {
                return $query->where(function ($subquery) use ($search) {
                    return $subquery->where('title', 'like', "%{$search}%")
                        ->orWhere('writer', 'like', "%{$search}%")
                        ->orWhereHas('category', function ($query) use ($search) {
                            return $query->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request['writer'], function ($query) use ($request) {
                return $query->where('writer', 'like', "%{$request['writer']}%");
            })
            ->when($request['category'], function ($query) use ($request) {
                return $query->whereHas('category', function ($subquery) use ($request) {
                    return $subquery->where('name', 'like', "%{$request['category']}%");
                });
            });

        $blogList = $this->getPriorityWiseBlogQuery(query: $blogListQuery, dataLimit: 7, appends: request()->all());
        $recentBlogList = Blog::active()->with(['category', 'translations'])->latest()->paginate(5);
        $blogCategoryList = BlogCategory::active()->orderBy('click_count', 'desc');
        $blogCategoryList = $this->getPriorityWiseBlogCategoryQuery(query: $blogCategoryList, dataLimit: 'all');

        return view(VIEW_FILE_NAMES['frontend_blog_list'], [
            'blogTitle' => $titleData[getDefaultLanguage()] ?? ($titleData['en'] ?? ''),
            'blogSubTitle' => $subTitleData[getDefaultLanguage()] ?? ($subTitleData['en'] ?? ''),
            'blogList' => $blogList,
            'recentBlogList' => $recentBlogList,
            'blogCategoryList' => $blogCategoryList,
            'blogPlatform' => strpos(url()->current(), '/app') ? 'app' : 'web',
            'blogListRoute' => strpos(url()->current(), '/app') ? 'app.blog.index' : 'frontend.blog.index',
            'popularBlogListRoute' => strpos(url()->current(), '/app') ? 'app.blog.popular-blog' : 'frontend.blog.popular-blog',
            'blogDetailsRoute' => strpos(url()->current(), '/app') ? 'app.blog.details' : 'frontend.blog.details',
        ]);
    }

    public function getDetailsView(Request $request): View|JsonResponse|RedirectResponse
    {
        $this->frontendBlogService->getCheckLocale(request: $request);
        $blogData = $this->blog->withoutGlobalScopes()->with(['category' => function ($query) {
            return $query->active();
        }, 'translations', 'seoInfo'])->where(['slug' => $request['slug']])->first();

        if (!$blogData) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => translate('Blog_not_found'),
                    'status' => 404,
                ]);
            }
            Toastr::error(translate('Blog_not_found'));
            return redirect()->back();
        }

        $draftData = json_decode($blogData['draft_data'] ?? '', true);
        if (\request('source') == 'draft' && !empty($draftData)) {
            $translatedData = $this->blogTranslation->where(['translation_id' => $blogData['id'], 'locale' => getDefaultLanguage(), 'is_draft' => 1])->get();
            $draftCategory = $this->blogCategory->where('id', ($draftData['category_id'] ?? 0))->first();
            $blogData->title = $translatedData?->firstWhere('key', 'title')?->value ?? $draftData['title'] ?? '';
            $blogData->description = $translatedData?->firstWhere('key', 'description')?->value ?? $draftData['description'] ?? '';
            $blogData->category = $draftCategory;
            $blogData->writer = $draftData['writer'] ?? '';
            $blogData->publish_date = $draftData['publish_date'] ?? '';
            $blogData->image = $blogData?->draft_image ?? null;
        }

        if (request('source') == '' && !in_array($blogData['id'], session('user_visited_blog_ids', []))) {
            session(['user_visited_blog_ids' => [$blogData['id']]]);
            $blogData->increment('click_count');
        }

        $popularBlogList = $this->blog->with(['category' => function ($query) {
            return $query->active();
        }, 'translations'])->latest()->paginate(3);
        $articleLinks = $this->frontendBlogService->getModifiedDescriptionLinks(description: $blogData['description']);
        $updatedDescription = $this->frontendBlogService->getModifiedDescription(description: $blogData['description']);

        return view(VIEW_FILE_NAMES['frontend_blog_details_view'], [
            'blogData' => $blogData,
            'popularBlogList' => $popularBlogList,
            'articleLinks' => $articleLinks,
            'updatedDescription' => $updatedDescription,
            'blogPlatform' => strpos(url()->current(), '/app') ? 'app' : 'web',
            'blogListRoute' => strpos(url()->current(), '/app') ? 'app.blog.index' : 'frontend.blog.index',
            'popularBlogListRoute' => strpos(url()->current(), '/app') ? 'app.blog.popular-blog' : 'frontend.blog.popular-blog',
            'blogDetailsRoute' => strpos(url()->current(), '/app') ? 'app.blog.details' : 'frontend.blog.details',
        ]);
    }

    public function getPopularBlogs(Request $request): View
    {
        $search = $request->input('search');
        $popularBlogList = Blog::active()->with(['category' => function ($query) {
            return $query->active();
        }, 'translations'])
            ->when($search, function ($query, $search) {
                $query->where(function ($subquery) use ($search) {
                    $subquery->where('title', 'like', "%{$search}%")
                        ->orWhere('writer', 'like', "%{$search}%")
                        ->orWhereHas('category', function ($query) use ($search) {
                            return $query->where('name', 'like', "%{$search}%");
                        });;
                });
            })
            ->when($request['writer'], function ($query) use ($request) {
                return $query->where('writer', 'like', "%{$request['writer']}%");
            })
            ->when($request['category'], function ($query) use ($request) {
                return $query->whereHas('category', function ($subquery) use ($request) {
                    return $subquery->where('name', 'like', "%{$request['category']}%");
                });
            })
            ->orderBy('click_count', 'desc')->latest()->paginate(6)->appends(request()->all());

        $recentBlogList = Blog::active()->with(['category', 'translations'])->latest()->paginate(5);
        $blogCategoryList = BlogCategory::active()->orderBy('click_count', 'desc');
        $blogCategoryList = $this->getPriorityWiseBlogCategoryQuery(query: $blogCategoryList, dataLimit: 'all');

        return view(VIEW_FILE_NAMES['frontend_popular_blogs_view'], [
            'popularBlogList' => $popularBlogList,
            'blogCategoryList' => $blogCategoryList,
            'recentBlogList' => $recentBlogList,
            'blogPlatform' => strpos(url()->current(), '/app') ? 'app' : 'web',
            'blogListRoute' => strpos(url()->current(), '/app') ? 'app.blog.index' : 'frontend.blog.index',
            'popularBlogListRoute' => strpos(url()->current(), '/app') ? 'app.blog.popular-blog' : 'frontend.blog.popular-blog',
            'blogDetailsRoute' => strpos(url()->current(), '/app') ? 'app.blog.details' : 'frontend.blog.details',
        ]);
    }
}
