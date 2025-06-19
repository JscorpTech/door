<?php
    $downloadAppStatus = getWebConfig(name: 'blog_feature_download_app_status') ?? 0;
    $appTitleData = getWebConfig(name: 'blog_feature_download_app_title') ?? [];
    $appSubTitleData = getWebConfig(name: 'blog_feature_download_app_subtitle') ?? [];
    $googleStoreLink = getWebConfig(name: 'download_app_google_stroe') ?? [];
    $appleStoreLink = getWebConfig(name: 'download_app_apple_stroe') ?? [];
    $downloadSectionData = [
        'title' => $appTitleData[getDefaultLanguage()] ?? ($appTitleData['en'] ?? ''),
        'subtitle' => $appSubTitleData[getDefaultLanguage()] ?? ($appSubTitleData['en'] ?? ''),
        'google_app_button' => getWebConfig(name: 'blog_feature_download_google_app_button_status') && (!empty($googleStoreLink) && $googleStoreLink['status'] && $googleStoreLink['link']) ? $googleStoreLink['link'] : '',
        'apple_app_button' => getWebConfig(name: 'blog_feature_download_apple_app_button_status') && (!empty($appleStoreLink) && $appleStoreLink['status'] && $appleStoreLink['link']) ? $appleStoreLink['link'] : '',
        'app_icon' => getWebConfig(name: 'blog_feature_download_app_icon'),
        'app_background' => getWebConfig(name: 'blog_feature_download_app_background'),
    ];
    $cardShowAble = 0;
    if ($downloadAppStatus && (
        isset($downloadSectionData['app_icon']['path']) ||
        !empty($downloadSectionData['title']) ||
        !empty($downloadSectionData['subtitle']) ||
        !empty($downloadSectionData['google_app_button']) ||
        !empty($downloadSectionData['apple_app_button'])
        )
    ) {
        $cardShowAble = 1;
    }
?>

@if($cardShowAble && isset($blogPlatform) && $blogPlatform == 'web')
    <div class="download-user-app text-center"
         data-bg-img="{{ isset($downloadSectionData['app_background']['path']) ? $downloadSectionData['app_background']['path'] : theme_asset(path: 'public/assets/front-end/img/blogs/download-user-app-bg.png') }}"
    >
        <div class="download-user-main">
            @isset($downloadSectionData['app_icon']['path'])
                <div class="mb-4">
                    <img width="60" height="60" class="rounded object-cover" src="{{ $downloadSectionData['app_icon']['path'] }}" alt="">
                </div>
            @endisset

            <h3 class="mb-12px text-white fs-22 fs-20-mobile">{{ $downloadSectionData['title'] }}</h3>
            <p class="mb-60 px-2 text-white fs-16 fs-14-mobile">{!! $downloadSectionData['subtitle'] !!}</p>
            <div class="d-flex justify-content-between flex-wrap gap-3 mb-3">

                @if($downloadSectionData['google_app_button'])
                    <a href="{{ $downloadSectionData['google_app_button'] }}" target="_blank"
                       class="rounded-10 bg-white px-3 py-2 d-flex justify-content-center align-items-center gap-3 flex-grow-1">
                        <img width="22" src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/play-store.svg') }}" alt="">
                        <span>{{ translate('Google_Play') }}</span>
                    </a>
                @endif

                @if($downloadSectionData['apple_app_button'])
                    <a href="{{ $downloadSectionData['apple_app_button'] }}" target="_blank"
                       class="rounded-10 bg-white px-3 py-2 d-flex justify-content-center align-items-center gap-3 flex-grow-1">
                        <img width="22" src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/apple-store.svg') }}" alt="">
                        <span>{{ translate('App_Store') }}</span>
                    </a>
                @endif

            </div>
        </div>
    </div>
@endif
