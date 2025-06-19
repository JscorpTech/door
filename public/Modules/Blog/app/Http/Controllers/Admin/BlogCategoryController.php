<?php

namespace Modules\Blog\app\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Blog\app\Http\Requests\BlogCategoryAddRequest;
use Modules\Blog\app\Http\Requests\BlogCategoryUpdateRequest;
use Modules\Blog\app\Models\BlogCategory;
use Modules\Blog\app\Services\BlogCategoryService;
use Modules\Blog\app\Traits\BlogCategoryTrait;

class BlogCategoryController extends Controller
{
    use BlogCategoryTrait;

    public function __construct(
        private readonly BlogCategory        $blogCategory,
        private readonly BlogCategoryService $blogCategoryService,
    )
    {
    }

    public function add(BlogCategoryAddRequest $request): jsonResponse
    {
        $blogCategory = $this->blogCategory->create($this->blogCategoryService->getAddData(request: $request));
        $this->addBlogCategoryTranslation(request: $request, id: $blogCategory->id);

        $result = $this->renderBlogCategoryList();
        return response()->json([
            'message' => translate('category_added_successfully'),
            'html' => $result['html'],
            'count' => $result['count'],
        ], 200);
    }

    public function update(BlogCategoryUpdateRequest $request): JsonResponse
    {
        $this->blogCategory->where('id', $request['id'])
            ->update($this->blogCategoryService->getUpdateData(request: $request));
        $this->updateBlogCategoryTranslation(request: $request, id: $request['id']);
        $result = $this->renderBlogCategoryList();

        return response()->json([
            'message' => translate('Category_updated_successfully.'),
            'html' => $result['html'],
            'count' => $result['count'],
        ]);
    }

    public function getCategoryInfo(Request $request): JsonResponse
    {
        $category = $this->blogCategory->withoutGlobalScopes()->with('translations')->findOrFail($request['id']);
        $categoryLang = $this->blogCategoryService->getCategoryLanguageData(category: $category);

        return response()->json([
            'data' => $category,
            'lang_data' => $categoryLang,
        ]);
    }

    public function updateStatus(Request $request): JsonResponse
    {
        $this->blogCategory->where('id', $request['category_id'])->update(['status' => ($request['status'] ?? 0)]);
        $result = $this->renderBlogCategoryList();

        return response()->json([
            'message' => translate('Status_updated_successfully.'),
            'html' => $result['html'],
            'count' => $result['count'],
        ]);
    }

    public function deleteCategory(Request $request): JsonResponse
    {
        $this->blogCategory->where('id',$request['category_id'])->delete();
        $result = $this->renderBlogCategoryList();

        return response()->json([
            'message' => translate('Category_deleted_successfully.'),
            'html' => $result['html'],
            'count' => $result['count'],
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $result = $this->renderBlogCategoryList($request['searchValue'], $request->get('page'));
        return response()->json([
            'html' => $result['html'],
            'count' => $result['count'],
        ]);
    }

    private function renderBlogCategoryList($search = null, $page = null): array
    {
        $categories = BlogCategory::query()
            ->with('translations')
            ->when($search, function ($query) use ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhereHas('translations', function($q) use ($search) {
                          $q->where('value', 'LIKE', "%{$search}%");
                      });
                });
            })
            ->orderBy('updated_at', 'desc')
            ->paginate(
                perPage: getWebConfig(name: 'pagination_limit'),
                columns: ['*'],
                pageName: 'page',
                page: $page
            )->withQueryString();

        $html = view('blog::admin-views.blog.category.partials.table-rows', compact('categories'))->render();
        return [
            'html' => $html,
            'count' => $categories->total(),
        ];
    }

    public function getList(Request $request): JsonResponse
    {
        $categories = $this->blogCategory->withoutGlobalScopes()->with('translations')->get();
        $dropdown = $this->blogCategoryService->getCategoryDropdown(request: $request, categories: $categories);
        return response()->json([
            'select_tag' => $dropdown,
        ]);
    }
}
