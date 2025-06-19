<?php

namespace Modules\Blog\app\Http\Controllers\Admin;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Traits\FileManagerTrait;
use App\Traits\SettingsTrait;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BlogDownloadAppController extends Controller
{
    use SettingsTrait;
    use FileManagerTrait {
        delete as deleteFile;
        update as updateFile;
    }

    public function __construct(
        private readonly BusinessSettingRepositoryInterface $businessSettingRepo,
    )
    {
    }

    public function appDownloadSetup(): View
    {
        $languages = getWebConfig(name: 'pnc_language') ?? null;
        $defaultLanguage = $languages[0];
        $web = $this->businessSettingRepo->getListWhere(dataLimit: 'all');
        $businessSetting = [
            'google_app_status' => $this->getSettings(object: $web, type: 'blog_feature_download_google_app_button_status')->value ?? '',
            'apple_app_status' => $this->getSettings(object: $web, type: 'blog_feature_download_apple_app_button_status')->value ?? '',
            'app_download_icon' => $this->getSettings(object: $web, type: 'blog_feature_download_app_icon')->value ?? '',
            'app_download_background' => $this->getSettings(object: $web, type: 'blog_feature_download_app_background')->value ?? '',
        ];
        $titleData = json_decode($this->getSettings(object: $web, type: 'blog_feature_download_app_title')?->value ?? '', true);
        $subTitleData = json_decode($this->getSettings(object: $web, type: 'blog_feature_download_app_subtitle')?->value ?? '', true);
        return view("blog::admin-views.blog.app-download-setup", compact('businessSetting', 'languages', 'defaultLanguage', 'titleData', 'subTitleData'));
    }

    public function updateDownloadAppButton(Request $request): RedirectResponse
    {
        $this->businessSettingRepo->updateOrInsert(type: 'blog_feature_download_app_title', value: json_encode($request['title'] ?? []));
        $this->businessSettingRepo->updateOrInsert(type: 'blog_feature_download_app_subtitle', value: json_encode($request['sub_title'] ?? []));
        $this->businessSettingRepo->updateOrInsert(type: 'blog_feature_download_google_app_button_status', value: $request['google_app_status'] ?? 0);
        $this->businessSettingRepo->updateOrInsert(type: 'blog_feature_download_apple_app_button_status', value: $request['apple_app_status'] ?? 0);

        $downloadAppIcon = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'blog_feature_download_app_icon']);
        if ($request->has('icon')) {
            $downloadAppIconImage = [
                'image_name' => $this->updateFile(dir: 'company/', oldImage: isset($downloadAppIcon['value'])
                    ? (is_array($downloadAppIcon['value'])
                        ? $downloadAppIcon['value']['image_name']
                        : $downloadAppIcon['value'])
                    : null, format: 'webp', image: $request->icon),
                'storage' => config('filesystems.disks.default') ?? 'public'
            ];
            $this->businessSettingRepo->updateOrInsert(type: 'blog_feature_download_app_icon', value: $downloadAppIconImage['image_name'] ?? 0);
        }

        $downloadAppBackground = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'blog_feature_download_app_background']);
        if ($request->has('image')) {
            $downloadAppIconImage = [
                'image_name' => $this->updateFile(dir: 'company/', oldImage: isset($downloadAppBackground['value'])
                    ? (is_array($downloadAppBackground['value'])
                        ? $downloadAppBackground['value']['image_name']
                        : $downloadAppBackground['value'])
                    : null, format: 'webp', image: $request->image),
                'storage' => config('filesystems.disks.default') ?? 'public'
            ];
            $this->businessSettingRepo->updateOrInsert(type: 'blog_feature_download_app_background', value: $downloadAppIconImage['image_name'] ?? 0);
        }

        Toastr::success(translate('updated_successfully'));
        return back();
    }

    public function updateStatus(Request $request): JsonResponse
    {
        $this->businessSettingRepo->updateOrInsert(type: 'blog_feature_download_app_status', value: $request['status'] ?? 0);
        return response()->json([
            'status' => true,
            'message' => translate('Status_updated_successfully')
        ]);
    }

    public function deleteImage(Request $request): RedirectResponse
    {
        if ($request['icon']) {
            $this->deleteFile(filePath: '/company/' . $request['icon']);
            $icon = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'blog_feature_download_app_icon']);
            $this->businessSettingRepo->delete(params: ['value' => $icon['value']]);
            Toastr::success(translate('app_download_icon_removed_successfully'));
        } else {
            Toastr::warning(translate('no_image_found_to_delete'));
        }
        if ($request['image']) {
            $this->deleteFile(filePath: '/company/' . $request['image']);
            $image = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'blog_feature_download_app_background']);
            $this->businessSettingRepo->delete(params: ['value' => $image['value']]);
            Toastr::success(translate('app_background_removed_successfully'));
        } else {
            Toastr::warning(translate('no_image_found_to_delete'));
        }
        return back();
    }

}
