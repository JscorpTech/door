<?php

namespace Modules\Blog\app\Http\Controllers\Admin;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Services\PrioritySetupService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class BlogPrioritySetupController extends Controller
{
    public function __construct(
        private readonly BusinessSettingRepositoryInterface $businessSettingRepo,
        private readonly PrioritySetupService               $prioritySetupService,
    )
    {
    }

    public function index(?Request $request, string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        return $this->getView();
    }

    public function getView(): View
    {
        $blogCategoryPriority = json_decode($this->businessSettingRepo->getFirstWhere(params: ['type' => 'blog_category_list_priority'])?->value ?? '', true);
        $blogPriority = json_decode($this->businessSettingRepo->getFirstWhere(params: ['type' => 'blog_list_priority'])?->value ?? '', true);
        return view('blog::admin-views.blog._priority-setup', compact('blogCategoryPriority', 'blogPriority'));
    }

    public function update(Request $request): RedirectResponse
    {
        return match ($request['type']) {
            'blog_category_list_priority' => $this->updateCategoryListPriority(request: $request),
            'blog_list_priority' => $this->updateBLogListPriority(request: $request),
        };
    }

    public function updateCategoryListPriority(Request $request): RedirectResponse
    {
        $request->validate([
            'sort_by' => 'required|in:most_clicked,a_to_z,z_to_a',
        ]);

        $this->businessSettingRepo->updateOrInsert(
            type: 'blog_category_list_priority',
            value: json_encode($this->prioritySetupService->updateBlogCategoryPrioritySetupData(request: $request))
        );
        Toastr::success(translate('blog_category_Priority_setup_updated_successfully'));
        return redirect()->back();
    }

    public function updateBLogListPriority(Request $request): RedirectResponse
    {
        $request->validate([
            'sort_by' => 'required|in:most_clicked,a_to_z,z_to_a',
        ]);

        $this->businessSettingRepo->updateOrInsert(
            type: 'blog_list_priority',
            value: json_encode($this->prioritySetupService->updateBlogPrioritySetupData(request: $request))
        );
        Toastr::success(translate('blog_Priority_setup_updated_successfully'));
        return redirect()->back();
    }
}
