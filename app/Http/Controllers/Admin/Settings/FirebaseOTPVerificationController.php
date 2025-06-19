<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Enums\GlobalConstant;
use App\Enums\ViewPaths\Admin\FirebaseOTPVerification;
use App\Enums\ViewPaths\Admin\SystemSetup;
use App\Http\Controllers\BaseController;
use App\Traits\FileManagerTrait;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class FirebaseOTPVerificationController extends BaseController
{
    use FileManagerTrait;

    public function __construct(
        private readonly BusinessSettingRepositoryInterface $businessSettingRepo,
    )
    {
    }

    public function index(?Request $request, string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        return $this->getView();
    }

    private function getView(): View
    {
        $firebaseOTPVerification = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'firebase_otp_verification'])?->value ?? '';
        $configStatus = $this->checkFirebaseConfigAbility();
        return view(FirebaseOTPVerification::INDEX[VIEW], [
            'firebaseOTPVerification' => json_decode($firebaseOTPVerification, true),
            'configStatus' => $configStatus,
        ]);
    }

    public function updateConfig(Request $request): RedirectResponse
    {
        if (env('APP_MODE') == 'demo') {
            Toastr::error(translate('you_can_not_update_this_on_demo_mode'));
            return back();
        }
        $this->businessSettingRepo->updateOrInsert(type: 'firebase_otp_verification', value: json_encode([
            'status' => $request->get('status', 0),
            'web_api_key' => $request['web_api_key'],
        ]));
        clearWebConfigCacheKeys();
        Toastr::success(translate('Update_successfully'));
        return back();
    }

    private function checkFirebaseConfigAbility(): bool
    {
        $config = getWebConfig('fcm_credentials') ?? [];
        $configStatus = true;

        if (
            empty($config) || empty($config['apiKey']) || empty($config['authDomain']) ||
            empty($config['projectId']) || empty($config['storageBucket']) || empty($config['messagingSenderId']) || empty($config['appId'])
        ) {
            $configStatus = false;
        }

        return $configStatus;
    }

    public function getConfigValidation(Request $request): JsonResponse
    {
        $status = 1;
        if ($request['key'] == 'firebase') {
            $status = $this->checkFirebaseConfigAbility();
        }

        $data = [
            'type' => $request['key'],
        ];
        return response()->json([
            'status' => $status,
            'htmlView' => view(FirebaseOTPVerification::FIREBASE_CONFIG_VALIDATION[VIEW], ['data' => $data])->render()
        ]);
    }

}
