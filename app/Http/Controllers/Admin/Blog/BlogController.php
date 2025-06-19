<?php

namespace App\Http\Controllers\Admin\Blog;

use App\Enums\ViewPaths\Admin\Banner;
use App\Enums\ViewPaths\Admin\Blog;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request|null $request, string $type = null): View
    {
        return $this->getListView($request);
    }

    public function getListView(Request $request): View
    {
        return view(Blog::LIST[VIEW]);
    }

    public function appDownloadSetup(): View
    {
        return view(Blog::APP_DOWNLOAD[VIEW]);
    }

    public function getAddView(): View
    {
        return view(Blog::ADD[VIEW]);
    }

    public function getUpdateView(): View
    {
        return view(Blog::UPDATE[VIEW]);
    }
    public function getDraftView(): View
    {
        return view(Blog::DRAFT[VIEW]);
    }
}
