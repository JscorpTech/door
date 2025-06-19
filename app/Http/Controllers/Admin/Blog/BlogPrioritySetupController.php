<?php

namespace App\Http\Controllers\Admin\Blog;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Enums\ViewPaths\Admin\Blog;
use App\Http\Controllers\Controller;
use App\Services\PrioritySetupService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BlogPrioritySetupController extends Controller
{
    public function __construct(
        private readonly BusinessSettingRepositoryInterface     $businessSettingRepo,
        private readonly PrioritySetupService                   $prioritySetupService,
    )
    {

    }
    public function prioritySetup(): View
    {
        $blogCategoryPriority = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'blog_category_list_priority'])?->value ?? '';
        $blogPriority = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'blog_list_priority'])?->value ?? '';
        return view(Blog::PRIORITY_SETUP[VIEW], [
            'blogCategoryPriority' => json_decode($blogCategoryPriority, true),
            'blogPriority' => json_decode($blogPriority, true),
        ]);
    }

    public function updateConfig(Request $request): RedirectResponse
    {
        $this->businessSettingRepo->updateOrInsert(
            type: 'blog_category_list_priority',
            value: json_encode($this->prioritySetupService->updateBlogCategoryPrioritySetupData(request: $request))
        );
        $this->businessSettingRepo->updateOrInsert(
            type: 'blog_list_priority',
            value: json_encode($this->prioritySetupService->updateBlogPrioritySetupData(request: $request))
        );
        Toastr::success(translate('Priority_setup_updated_successfully'));
        return redirect()->back();
    }
}
