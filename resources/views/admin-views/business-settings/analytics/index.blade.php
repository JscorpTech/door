@extends('layouts.back-end.app')
@section('title', translate('analytics_Script'))
@push('css_or_js')
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/vendor/swiper/swiper-bundle.min.css') }}" />
@endpush
@section('content')
    <div class="content container-fluid">
        <div class="mb-3 d-flex justify-content-between gap-2">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/marketing-tool-icon.svg') }}" alt="">
                {{ translate('Marketing_Tool') }}
            </h2>

            <button class="btn-how-it-works d-flex align-items-center gap-2 font-weight-medium text-capitalize cursor-pointer"
                data-toggle="modal" data-target="#getInformationModal">
                <div class="ripple-animation text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"
                        fill="none">
                        <g clip-path="url(#clip0_10137_36912)">
                            <path
                                d="M8 0C6.41775 0 4.87104 0.469192 3.55544 1.34824C2.23985 2.22729 1.21447 3.47672 0.608967 4.93853C0.00346629 6.40034 -0.15496 8.00887 0.153721 9.56072C0.462403 11.1126 1.22433 12.538 2.34315 13.6569C3.46197 14.7757 4.88743 15.5376 6.43928 15.8463C7.99113 16.155 9.59966 15.9965 11.0615 15.391C12.5233 14.7855 13.7727 13.7602 14.6518 12.4446C15.5308 11.129 16 9.58225 16 8C15.9977 5.87897 15.1541 3.84547 13.6543 2.34568C12.1545 0.845886 10.121 0.00229405 8 0ZM8 14.6667C6.68146 14.6667 5.39253 14.2757 4.2962 13.5431C3.19987 12.8106 2.34539 11.7694 1.84081 10.5512C1.33622 9.33305 1.2042 7.99261 1.46144 6.6994C1.71867 5.40619 2.35361 4.21831 3.28596 3.28596C4.21831 2.35361 5.4062 1.71867 6.6994 1.46143C7.99261 1.2042 9.33305 1.33622 10.5512 1.8408C11.7694 2.34539 12.8106 3.19987 13.5431 4.2962C14.2757 5.39253 14.6667 6.68146 14.6667 8C14.6647 9.76752 13.9617 11.4621 12.7119 12.7119C11.4621 13.9617 9.76752 14.6647 8 14.6667Z"
                                fill="currentColor" />
                            <path
                                d="M8.47816 3.37534C8.09372 3.3053 7.69857 3.3206 7.32069 3.42017C6.94281 3.51974 6.59144 3.70115 6.29143 3.95155C5.99142 4.20195 5.7501 4.51522 5.58457 4.86921C5.41903 5.22319 5.33332 5.60923 5.3335 6.00001C5.3335 6.17682 5.40373 6.34639 5.52876 6.47142C5.65378 6.59644 5.82335 6.66668 6.00016 6.66668C6.17697 6.66668 6.34654 6.59644 6.47157 6.47142C6.59659 6.34639 6.66683 6.17682 6.66683 6.00001C6.66666 5.80386 6.70977 5.61009 6.79309 5.43252C6.87641 5.25494 6.99788 5.09794 7.14884 4.9727C7.2998 4.84746 7.47654 4.75707 7.66645 4.70797C7.85635 4.65888 8.05475 4.65229 8.2475 4.68868C8.51086 4.7398 8.753 4.86827 8.943 5.05767C9.13299 5.24707 9.26222 5.48881 9.31416 5.75201C9.36663 6.02828 9.3304 6.31406 9.21067 6.5685C9.09093 6.82294 8.89381 7.03301 8.6475 7.16868C8.23961 7.40499 7.90255 7.74635 7.67145 8.15721C7.44034 8.56807 7.32364 9.03338 7.3335 9.50468V10C7.3335 10.1768 7.40373 10.3464 7.52876 10.4714C7.65378 10.5964 7.82335 10.6667 8.00016 10.6667C8.17697 10.6667 8.34654 10.5964 8.47157 10.4714C8.59659 10.3464 8.66683 10.1768 8.66683 10V9.50468C8.65846 9.27269 8.71137 9.04258 8.82021 8.83754C8.92905 8.63249 9.08999 8.45974 9.28683 8.33668C9.76984 8.07139 10.1588 7.66298 10.4002 7.16761C10.6416 6.67225 10.7237 6.11425 10.635 5.57036C10.5464 5.02647 10.2914 4.5234 9.90516 4.13034C9.51893 3.73728 9.02041 3.47352 8.47816 3.37534Z"
                                fill="currentColor" />
                            <path
                                d="M8.66683 12C8.66683 11.6318 8.36835 11.3333 8.00016 11.3333C7.63197 11.3333 7.3335 11.6318 7.3335 12C7.3335 12.3682 7.63197 12.6666 8.00016 12.6666C8.36835 12.6666 8.66683 12.3682 8.66683 12Z"
                                fill="currentColor" />
                        </g>
                        <defs>
                            <clipPath id="clip0_10137_36912">
                                <rect width="16" height="16" fill="white" />
                            </clipPath>
                        </defs>
                    </svg>
                </div>
                <span class="text--black">
                    {{ translate('how_it_work') }}
                </span>
            </button>
        </div>

        <div class="row gy-3">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        @php($googleAnalytics = $analyticsData['google_analytics'] ?? null)
                        <form
                            action="{{ env('APP_MODE') != 'demo' ? route('admin.business-settings.analytics-update') : 'javascript:' }}"
                            method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="d-flex justify-content-between gap-2 mb-5">
                                <h4 class="mb-0 d-flex gap-1 title-color fz-16">
                                    {{ translate('Google_Analytics') }}
                                </h4>
                                <div class="toggle-switch-with-text">
                                    <label class="switch--custom-label toggle-switch toggle-switch-sm d-inline-flex">
                                        <input type="checkbox" name="is_active" value="1"
                                               class="toggle-switch-input" {{ $googleAnalytics?->is_active == 1 ? 'checked' : '' }}>

                                        <span class="toggle-switch-label text">
                                            <span
                                                class="toggle-switch-indicator d-flex align-items-center justify-content-center">
                                                <span>
                                                </span>
                                            </span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="title-color d-flex font-weight-medium">{{ translate('Google_Analytics_Measurement_ID') }}</label>
                                <input type="hidden" name="type" value="google_analytics">
                                <textarea type="text" placeholder="{{ translate('Enter_the_GA_Measurement_ID') }}"
                                          class="form-control __h-45px" name="script_id">{!! $googleAnalytics?->script_id ?? '' !!}</textarea>
                            </div>
                            <div class="d-flex gap-3 justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2 title-color font-weight-medium text-capitalize cursor-pointer" data-toggle="modal"
                                     data-target="#modalForGoogleAnalytics">
                                    <div class="ripple-animation">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                             viewBox="0 0 16 16" fill="none">
                                            <g clip-path="url(#clip0_10137_36912)">
                                                <path
                                                    d="M8 0C6.41775 0 4.87104 0.469192 3.55544 1.34824C2.23985 2.22729 1.21447 3.47672 0.608967 4.93853C0.00346629 6.40034 -0.15496 8.00887 0.153721 9.56072C0.462403 11.1126 1.22433 12.538 2.34315 13.6569C3.46197 14.7757 4.88743 15.5376 6.43928 15.8463C7.99113 16.155 9.59966 15.9965 11.0615 15.391C12.5233 14.7855 13.7727 13.7602 14.6518 12.4446C15.5308 11.129 16 9.58225 16 8C15.9977 5.87897 15.1541 3.84547 13.6543 2.34568C12.1545 0.845886 10.121 0.00229405 8 0ZM8 14.6667C6.68146 14.6667 5.39253 14.2757 4.2962 13.5431C3.19987 12.8106 2.34539 11.7694 1.84081 10.5512C1.33622 9.33305 1.2042 7.99261 1.46144 6.6994C1.71867 5.40619 2.35361 4.21831 3.28596 3.28596C4.21831 2.35361 5.4062 1.71867 6.6994 1.46143C7.99261 1.2042 9.33305 1.33622 10.5512 1.8408C11.7694 2.34539 12.8106 3.19987 13.5431 4.2962C14.2757 5.39253 14.6667 6.68146 14.6667 8C14.6647 9.76752 13.9617 11.4621 12.7119 12.7119C11.4621 13.9617 9.76752 14.6647 8 14.6667Z"
                                                    fill="currentColor" />
                                                <path
                                                    d="M8.47816 3.37534C8.09372 3.3053 7.69857 3.3206 7.32069 3.42017C6.94281 3.51974 6.59144 3.70115 6.29143 3.95155C5.99142 4.20195 5.7501 4.51522 5.58457 4.86921C5.41903 5.22319 5.33332 5.60923 5.3335 6.00001C5.3335 6.17682 5.40373 6.34639 5.52876 6.47142C5.65378 6.59644 5.82335 6.66668 6.00016 6.66668C6.17697 6.66668 6.34654 6.59644 6.47157 6.47142C6.59659 6.34639 6.66683 6.17682 6.66683 6.00001C6.66666 5.80386 6.70977 5.61009 6.79309 5.43252C6.87641 5.25494 6.99788 5.09794 7.14884 4.9727C7.2998 4.84746 7.47654 4.75707 7.66645 4.70797C7.85635 4.65888 8.05475 4.65229 8.2475 4.68868C8.51086 4.7398 8.753 4.86827 8.943 5.05767C9.13299 5.24707 9.26222 5.48881 9.31416 5.75201C9.36663 6.02828 9.3304 6.31406 9.21067 6.5685C9.09093 6.82294 8.89381 7.03301 8.6475 7.16868C8.23961 7.40499 7.90255 7.74635 7.67145 8.15721C7.44034 8.56807 7.32364 9.03338 7.3335 9.50468V10C7.3335 10.1768 7.40373 10.3464 7.52876 10.4714C7.65378 10.5964 7.82335 10.6667 8.00016 10.6667C8.17697 10.6667 8.34654 10.5964 8.47157 10.4714C8.59659 10.3464 8.66683 10.1768 8.66683 10V9.50468C8.65846 9.27269 8.71137 9.04258 8.82021 8.83754C8.92905 8.63249 9.08999 8.45974 9.28683 8.33668C9.76984 8.07139 10.1588 7.66298 10.4002 7.16761C10.6416 6.67225 10.7237 6.11425 10.635 5.57036C10.5464 5.02647 10.2914 4.5234 9.90516 4.13034C9.51893 3.73728 9.02041 3.47352 8.47816 3.37534Z"
                                                    fill="currentColor" />
                                                <path
                                                    d="M8.66683 12C8.66683 11.6318 8.36835 11.3333 8.00016 11.3333C7.63197 11.3333 7.3335 11.6318 7.3335 12C7.3335 12.3682 7.63197 12.6666 8.00016 12.6666C8.36835 12.6666 8.66683 12.3682 8.66683 12Z"
                                                    fill="currentColor" />
                                            </g>
                                            <defs>
                                                <clipPath id="clip0_10137_36912">
                                                    <rect width="16" height="16" fill="white" />
                                                </clipPath>
                                            </defs>
                                        </svg>
                                    </div>
                                    <span class="opacity--80">
                                        {{ translate('how_it_work') }}
                                    </span>
                                </div>
                                <button type="{{ env('APP_MODE') != 'demo' ? 'submit' : 'button' }}"
                                        class="btn btn--primary px-5 {{ env('APP_MODE') != 'demo' ? '' : 'call-demo' }}"
                                        >{{ translate('submit') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        @php($googleTagManager = $analyticsData['google_tag_manager'] ?? null)
                        <form
                            action="{{ env('APP_MODE') != 'demo' ? route('admin.business-settings.analytics-update') : 'javascript:' }}"
                            method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="d-flex justify-content-between gap-2 mb-5">
                                <h4 class="mb-0 d-flex gap-1 title-color fz-16">
                                    {{ translate('Google_Tag_Manager') }}
                                </h4>
                                <div class="toggle-switch-with-text">
                                    <label class="switch--custom-label toggle-switch toggle-switch-sm d-inline-flex">
                                        <input type="checkbox" name="is_active" value="1"
                                               class="toggle-switch-input" {{ $googleTagManager?->is_active == 1 ? 'checked' : '' }}>

                                        <span class="toggle-switch-label text">
                                            <span
                                                class="toggle-switch-indicator d-flex align-items-center justify-content-center">
                                                <span>
                                                </span>
                                            </span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="title-color d-flex font-weight-medium">{{ translate('Google_Tag_Manager_Container_ID') }}</label>
                                <input type="hidden" name="type" value="google_tag_manager">
                                <textarea type="text" placeholder="{{ translate('enter_the_GTM_Container_ID') }}"
                                          class="form-control __h-45px" name="script_id">{!! $googleTagManager?->script_id ?? '' !!}</textarea>
                            </div>
                            <div class="d-flex gap-3 justify-content-between align-items-center">
                                <div
                                    class="d-flex align-items-center gap-2 title-color font-weight-medium text-capitalize cursor-pointer" data-toggle="modal"
                                    data-target="#modalForGoogleTagManager">
                                    <div class="ripple-animation">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                             viewBox="0 0 16 16" fill="none">
                                            <g clip-path="url(#clip0_10137_36912)">
                                                <path
                                                    d="M8 0C6.41775 0 4.87104 0.469192 3.55544 1.34824C2.23985 2.22729 1.21447 3.47672 0.608967 4.93853C0.00346629 6.40034 -0.15496 8.00887 0.153721 9.56072C0.462403 11.1126 1.22433 12.538 2.34315 13.6569C3.46197 14.7757 4.88743 15.5376 6.43928 15.8463C7.99113 16.155 9.59966 15.9965 11.0615 15.391C12.5233 14.7855 13.7727 13.7602 14.6518 12.4446C15.5308 11.129 16 9.58225 16 8C15.9977 5.87897 15.1541 3.84547 13.6543 2.34568C12.1545 0.845886 10.121 0.00229405 8 0ZM8 14.6667C6.68146 14.6667 5.39253 14.2757 4.2962 13.5431C3.19987 12.8106 2.34539 11.7694 1.84081 10.5512C1.33622 9.33305 1.2042 7.99261 1.46144 6.6994C1.71867 5.40619 2.35361 4.21831 3.28596 3.28596C4.21831 2.35361 5.4062 1.71867 6.6994 1.46143C7.99261 1.2042 9.33305 1.33622 10.5512 1.8408C11.7694 2.34539 12.8106 3.19987 13.5431 4.2962C14.2757 5.39253 14.6667 6.68146 14.6667 8C14.6647 9.76752 13.9617 11.4621 12.7119 12.7119C11.4621 13.9617 9.76752 14.6647 8 14.6667Z"
                                                    fill="currentColor" />
                                                <path
                                                    d="M8.47816 3.37534C8.09372 3.3053 7.69857 3.3206 7.32069 3.42017C6.94281 3.51974 6.59144 3.70115 6.29143 3.95155C5.99142 4.20195 5.7501 4.51522 5.58457 4.86921C5.41903 5.22319 5.33332 5.60923 5.3335 6.00001C5.3335 6.17682 5.40373 6.34639 5.52876 6.47142C5.65378 6.59644 5.82335 6.66668 6.00016 6.66668C6.17697 6.66668 6.34654 6.59644 6.47157 6.47142C6.59659 6.34639 6.66683 6.17682 6.66683 6.00001C6.66666 5.80386 6.70977 5.61009 6.79309 5.43252C6.87641 5.25494 6.99788 5.09794 7.14884 4.9727C7.2998 4.84746 7.47654 4.75707 7.66645 4.70797C7.85635 4.65888 8.05475 4.65229 8.2475 4.68868C8.51086 4.7398 8.753 4.86827 8.943 5.05767C9.13299 5.24707 9.26222 5.48881 9.31416 5.75201C9.36663 6.02828 9.3304 6.31406 9.21067 6.5685C9.09093 6.82294 8.89381 7.03301 8.6475 7.16868C8.23961 7.40499 7.90255 7.74635 7.67145 8.15721C7.44034 8.56807 7.32364 9.03338 7.3335 9.50468V10C7.3335 10.1768 7.40373 10.3464 7.52876 10.4714C7.65378 10.5964 7.82335 10.6667 8.00016 10.6667C8.17697 10.6667 8.34654 10.5964 8.47157 10.4714C8.59659 10.3464 8.66683 10.1768 8.66683 10V9.50468C8.65846 9.27269 8.71137 9.04258 8.82021 8.83754C8.92905 8.63249 9.08999 8.45974 9.28683 8.33668C9.76984 8.07139 10.1588 7.66298 10.4002 7.16761C10.6416 6.67225 10.7237 6.11425 10.635 5.57036C10.5464 5.02647 10.2914 4.5234 9.90516 4.13034C9.51893 3.73728 9.02041 3.47352 8.47816 3.37534Z"
                                                    fill="currentColor" />
                                                <path
                                                    d="M8.66683 12C8.66683 11.6318 8.36835 11.3333 8.00016 11.3333C7.63197 11.3333 7.3335 11.6318 7.3335 12C7.3335 12.3682 7.63197 12.6666 8.00016 12.6666C8.36835 12.6666 8.66683 12.3682 8.66683 12Z"
                                                    fill="currentColor" />
                                            </g>
                                            <defs>
                                                <clipPath id="clip0_10137_36912">
                                                    <rect width="16" height="16" fill="white" />
                                                </clipPath>
                                            </defs>
                                        </svg>
                                    </div>
                                    <span class="opacity--80">
                                        {{ translate('how_it_work') }}
                                    </span>
                                </div>
                                <button type="{{ env('APP_MODE') != 'demo' ? 'submit' : 'button' }}"
                                        class="btn btn--primary px-5 {{ env('APP_MODE') != 'demo' ? '' : 'call-demo' }}"
                                        >{{ translate('submit') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        @php($linkedinInsight = $analyticsData['linkedin_insight'] ?? null)
                        <form
                            action="{{ env('APP_MODE') != 'demo' ? route('admin.business-settings.analytics-update') : 'javascript:' }}"
                            method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="d-flex justify-content-between gap-2 mb-5">
                                <h4 class="mb-0 d-flex gap-1 title-color fz-16">
                                    {{ translate('LinkedIn_Insight_Tag') }}
                                </h4>
                                <div class="toggle-switch-with-text">
                                    <label class="switch--custom-label toggle-switch toggle-switch-sm d-inline-flex">
                                        <input type="checkbox" name="is_active" value="1"
                                               class="toggle-switch-input" {{ $linkedinInsight?->is_active == 1 ? 'checked' : '' }}>

                                        <span class="toggle-switch-label text">
                                            <span
                                                class="toggle-switch-indicator d-flex align-items-center justify-content-center">
                                                <span>
                                                </span>
                                            </span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="title-color d-flex font-weight-medium">{{ translate('LinkedIn_Partner_ID') }}</label>
                                <input type="hidden" name="type" value="linkedin_insight">
                                <textarea type="text" placeholder="{{ translate('Enter_the_LinkedIn_Partner_ID') }}"
                                          class="form-control __h-45px" name="script_id">{!! $linkedinInsight?->script_id ?? '' !!}</textarea>
                            </div>
                            <div class="d-flex gap-3 justify-content-between align-items-center">
                                <div
                                    class="d-flex align-items-center gap-2 title-color font-weight-medium text-capitalize cursor-pointer" data-toggle="modal"
                                    data-target="#modalForLinkedInInsight">
                                    <div class="ripple-animation">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                             viewBox="0 0 16 16" fill="none">
                                            <g clip-path="url(#clip0_10137_36912)">
                                                <path
                                                    d="M8 0C6.41775 0 4.87104 0.469192 3.55544 1.34824C2.23985 2.22729 1.21447 3.47672 0.608967 4.93853C0.00346629 6.40034 -0.15496 8.00887 0.153721 9.56072C0.462403 11.1126 1.22433 12.538 2.34315 13.6569C3.46197 14.7757 4.88743 15.5376 6.43928 15.8463C7.99113 16.155 9.59966 15.9965 11.0615 15.391C12.5233 14.7855 13.7727 13.7602 14.6518 12.4446C15.5308 11.129 16 9.58225 16 8C15.9977 5.87897 15.1541 3.84547 13.6543 2.34568C12.1545 0.845886 10.121 0.00229405 8 0ZM8 14.6667C6.68146 14.6667 5.39253 14.2757 4.2962 13.5431C3.19987 12.8106 2.34539 11.7694 1.84081 10.5512C1.33622 9.33305 1.2042 7.99261 1.46144 6.6994C1.71867 5.40619 2.35361 4.21831 3.28596 3.28596C4.21831 2.35361 5.4062 1.71867 6.6994 1.46143C7.99261 1.2042 9.33305 1.33622 10.5512 1.8408C11.7694 2.34539 12.8106 3.19987 13.5431 4.2962C14.2757 5.39253 14.6667 6.68146 14.6667 8C14.6647 9.76752 13.9617 11.4621 12.7119 12.7119C11.4621 13.9617 9.76752 14.6647 8 14.6667Z"
                                                    fill="currentColor" />
                                                <path
                                                    d="M8.47816 3.37534C8.09372 3.3053 7.69857 3.3206 7.32069 3.42017C6.94281 3.51974 6.59144 3.70115 6.29143 3.95155C5.99142 4.20195 5.7501 4.51522 5.58457 4.86921C5.41903 5.22319 5.33332 5.60923 5.3335 6.00001C5.3335 6.17682 5.40373 6.34639 5.52876 6.47142C5.65378 6.59644 5.82335 6.66668 6.00016 6.66668C6.17697 6.66668 6.34654 6.59644 6.47157 6.47142C6.59659 6.34639 6.66683 6.17682 6.66683 6.00001C6.66666 5.80386 6.70977 5.61009 6.79309 5.43252C6.87641 5.25494 6.99788 5.09794 7.14884 4.9727C7.2998 4.84746 7.47654 4.75707 7.66645 4.70797C7.85635 4.65888 8.05475 4.65229 8.2475 4.68868C8.51086 4.7398 8.753 4.86827 8.943 5.05767C9.13299 5.24707 9.26222 5.48881 9.31416 5.75201C9.36663 6.02828 9.3304 6.31406 9.21067 6.5685C9.09093 6.82294 8.89381 7.03301 8.6475 7.16868C8.23961 7.40499 7.90255 7.74635 7.67145 8.15721C7.44034 8.56807 7.32364 9.03338 7.3335 9.50468V10C7.3335 10.1768 7.40373 10.3464 7.52876 10.4714C7.65378 10.5964 7.82335 10.6667 8.00016 10.6667C8.17697 10.6667 8.34654 10.5964 8.47157 10.4714C8.59659 10.3464 8.66683 10.1768 8.66683 10V9.50468C8.65846 9.27269 8.71137 9.04258 8.82021 8.83754C8.92905 8.63249 9.08999 8.45974 9.28683 8.33668C9.76984 8.07139 10.1588 7.66298 10.4002 7.16761C10.6416 6.67225 10.7237 6.11425 10.635 5.57036C10.5464 5.02647 10.2914 4.5234 9.90516 4.13034C9.51893 3.73728 9.02041 3.47352 8.47816 3.37534Z"
                                                    fill="currentColor" />
                                                <path
                                                    d="M8.66683 12C8.66683 11.6318 8.36835 11.3333 8.00016 11.3333C7.63197 11.3333 7.3335 11.6318 7.3335 12C7.3335 12.3682 7.63197 12.6666 8.00016 12.6666C8.36835 12.6666 8.66683 12.3682 8.66683 12Z"
                                                    fill="currentColor" />
                                            </g>
                                            <defs>
                                                <clipPath id="clip0_10137_36912">
                                                    <rect width="16" height="16" fill="white" />
                                                </clipPath>
                                            </defs>
                                        </svg>
                                    </div>

                                    <span class="opacity--80">
                                        {{ translate('how_it_work') }}
                                    </span>
                                </div>
                                <button type="{{ env('APP_MODE') != 'demo' ? 'submit' : 'button' }}"
                                        class="btn btn--primary px-5 {{ env('APP_MODE') != 'demo' ? '' : 'call-demo' }}"
                                        >{{ translate('submit') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        @php($metaPixel = $analyticsData['meta_pixel'] ?? null)
                        <form
                            action="{{ env('APP_MODE') != 'demo' ? route('admin.business-settings.analytics-update') : 'javascript:' }}"
                            method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="d-flex justify-content-between gap-2 mb-5">
                                <h4 class="mb-0 d-flex gap-1 title-color fz-16">
                                    {{ translate('Meta_Pixel') }}
                                </h4>
                                <div class="toggle-switch-with-text">
                                    <label class="switch--custom-label toggle-switch toggle-switch-sm d-inline-flex">
                                        <input type="checkbox" name="is_active" value="1" class="toggle-switch-input"
                                               {{ $metaPixel?->is_active == 1 ? 'checked' : '' }}>

                                        <span class="toggle-switch-label text">
                                            <span
                                                class="toggle-switch-indicator d-flex align-items-center justify-content-center">
                                                <span>
                                                </span>
                                            </span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="title-color d-flex font-weight-medium">{{ translate('Meta_Pixel_ID') }}</label>
                                <input type="hidden" name="type" value="meta_pixel">
                                <textarea type="text" placeholder="{{ translate('Enter_the_Meta_Pixel_ID') }}"
                                    class="form-control __h-45px" name="script_id">{!! $metaPixel?->script_id ?? '' !!}</textarea>
                            </div>
                            <div class="d-flex gap-3 justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2 title-color font-weight-medium text-capitalize cursor-pointer" data-toggle="modal"
                                     data-target="#modalForFacebookMeta">
                                    <div class="ripple-animation">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 16 16" fill="none">
                                            <g clip-path="url(#clip0_10137_36912)">
                                                <path
                                                    d="M8 0C6.41775 0 4.87104 0.469192 3.55544 1.34824C2.23985 2.22729 1.21447 3.47672 0.608967 4.93853C0.00346629 6.40034 -0.15496 8.00887 0.153721 9.56072C0.462403 11.1126 1.22433 12.538 2.34315 13.6569C3.46197 14.7757 4.88743 15.5376 6.43928 15.8463C7.99113 16.155 9.59966 15.9965 11.0615 15.391C12.5233 14.7855 13.7727 13.7602 14.6518 12.4446C15.5308 11.129 16 9.58225 16 8C15.9977 5.87897 15.1541 3.84547 13.6543 2.34568C12.1545 0.845886 10.121 0.00229405 8 0ZM8 14.6667C6.68146 14.6667 5.39253 14.2757 4.2962 13.5431C3.19987 12.8106 2.34539 11.7694 1.84081 10.5512C1.33622 9.33305 1.2042 7.99261 1.46144 6.6994C1.71867 5.40619 2.35361 4.21831 3.28596 3.28596C4.21831 2.35361 5.4062 1.71867 6.6994 1.46143C7.99261 1.2042 9.33305 1.33622 10.5512 1.8408C11.7694 2.34539 12.8106 3.19987 13.5431 4.2962C14.2757 5.39253 14.6667 6.68146 14.6667 8C14.6647 9.76752 13.9617 11.4621 12.7119 12.7119C11.4621 13.9617 9.76752 14.6647 8 14.6667Z"
                                                    fill="currentColor" />
                                                <path
                                                    d="M8.47816 3.37534C8.09372 3.3053 7.69857 3.3206 7.32069 3.42017C6.94281 3.51974 6.59144 3.70115 6.29143 3.95155C5.99142 4.20195 5.7501 4.51522 5.58457 4.86921C5.41903 5.22319 5.33332 5.60923 5.3335 6.00001C5.3335 6.17682 5.40373 6.34639 5.52876 6.47142C5.65378 6.59644 5.82335 6.66668 6.00016 6.66668C6.17697 6.66668 6.34654 6.59644 6.47157 6.47142C6.59659 6.34639 6.66683 6.17682 6.66683 6.00001C6.66666 5.80386 6.70977 5.61009 6.79309 5.43252C6.87641 5.25494 6.99788 5.09794 7.14884 4.9727C7.2998 4.84746 7.47654 4.75707 7.66645 4.70797C7.85635 4.65888 8.05475 4.65229 8.2475 4.68868C8.51086 4.7398 8.753 4.86827 8.943 5.05767C9.13299 5.24707 9.26222 5.48881 9.31416 5.75201C9.36663 6.02828 9.3304 6.31406 9.21067 6.5685C9.09093 6.82294 8.89381 7.03301 8.6475 7.16868C8.23961 7.40499 7.90255 7.74635 7.67145 8.15721C7.44034 8.56807 7.32364 9.03338 7.3335 9.50468V10C7.3335 10.1768 7.40373 10.3464 7.52876 10.4714C7.65378 10.5964 7.82335 10.6667 8.00016 10.6667C8.17697 10.6667 8.34654 10.5964 8.47157 10.4714C8.59659 10.3464 8.66683 10.1768 8.66683 10V9.50468C8.65846 9.27269 8.71137 9.04258 8.82021 8.83754C8.92905 8.63249 9.08999 8.45974 9.28683 8.33668C9.76984 8.07139 10.1588 7.66298 10.4002 7.16761C10.6416 6.67225 10.7237 6.11425 10.635 5.57036C10.5464 5.02647 10.2914 4.5234 9.90516 4.13034C9.51893 3.73728 9.02041 3.47352 8.47816 3.37534Z"
                                                    fill="currentColor" />
                                                <path
                                                    d="M8.66683 12C8.66683 11.6318 8.36835 11.3333 8.00016 11.3333C7.63197 11.3333 7.3335 11.6318 7.3335 12C7.3335 12.3682 7.63197 12.6666 8.00016 12.6666C8.36835 12.6666 8.66683 12.3682 8.66683 12Z"
                                                    fill="currentColor" />
                                            </g>
                                            <defs>
                                                <clipPath id="clip0_10137_36912">
                                                    <rect width="16" height="16" fill="white" />
                                                </clipPath>
                                            </defs>
                                        </svg>
                                    </div>
                                    <span class="opacity--80">
                                        {{ translate('how_it_work') }}
                                    </span>
                                </div>
                                <button type="{{ env('APP_MODE') != 'demo' ? 'submit' : 'button' }}"
                                    class="btn btn--primary px-5 {{ env('APP_MODE') != 'demo' ? '' : 'call-demo' }}">
                                    {{ translate('submit') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        @php($pinterestTag = $analyticsData['pinterest_tag'] ?? null)
                        <form
                            action="{{ env('APP_MODE') != 'demo' ? route('admin.business-settings.analytics-update') : 'javascript:' }}"
                            method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="d-flex justify-content-between gap-2 mb-5">
                                <h4 class="mb-0 d-flex gap-1 title-color fz-16">
                                    {{ translate('Pinterest_Pixel') }}
                                </h4>
                                <div class="toggle-switch-with-text">
                                    <label class="switch--custom-label toggle-switch toggle-switch-sm d-inline-flex">
                                        <input type="checkbox" name="is_active" value="1"
                                               class="toggle-switch-input" {{ $pinterestTag?->is_active == 1 ? 'checked' : '' }}>

                                        <span class="toggle-switch-label text">
                                            <span
                                                class="toggle-switch-indicator d-flex align-items-center justify-content-center">
                                                <span>
                                                </span>
                                            </span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="title-color d-flex font-weight-medium">{{ translate('Pinterest_Tag_ID') }}</label>
                                <input type="hidden" name="type" value="pinterest_tag">
                                <textarea type="text" placeholder="{{ translate('Enter_the_Pinterest_Tag_ID') }}"
                                          class="form-control __h-45px" name="script_id">{!! $pinterestTag?->script_id ?? '' !!}</textarea>
                            </div>
                            <div class="d-flex gap-3 justify-content-between align-items-center">
                                <div
                                    class="d-flex align-items-center gap-2 title-color font-weight-medium text-capitalize cursor-pointer" data-toggle="modal"
                                    data-target="#modalForPinterestPixel">
                                    <div class="ripple-animation">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                             viewBox="0 0 16 16" fill="none">
                                            <g clip-path="url(#clip0_10137_36912)">
                                                <path
                                                    d="M8 0C6.41775 0 4.87104 0.469192 3.55544 1.34824C2.23985 2.22729 1.21447 3.47672 0.608967 4.93853C0.00346629 6.40034 -0.15496 8.00887 0.153721 9.56072C0.462403 11.1126 1.22433 12.538 2.34315 13.6569C3.46197 14.7757 4.88743 15.5376 6.43928 15.8463C7.99113 16.155 9.59966 15.9965 11.0615 15.391C12.5233 14.7855 13.7727 13.7602 14.6518 12.4446C15.5308 11.129 16 9.58225 16 8C15.9977 5.87897 15.1541 3.84547 13.6543 2.34568C12.1545 0.845886 10.121 0.00229405 8 0ZM8 14.6667C6.68146 14.6667 5.39253 14.2757 4.2962 13.5431C3.19987 12.8106 2.34539 11.7694 1.84081 10.5512C1.33622 9.33305 1.2042 7.99261 1.46144 6.6994C1.71867 5.40619 2.35361 4.21831 3.28596 3.28596C4.21831 2.35361 5.4062 1.71867 6.6994 1.46143C7.99261 1.2042 9.33305 1.33622 10.5512 1.8408C11.7694 2.34539 12.8106 3.19987 13.5431 4.2962C14.2757 5.39253 14.6667 6.68146 14.6667 8C14.6647 9.76752 13.9617 11.4621 12.7119 12.7119C11.4621 13.9617 9.76752 14.6647 8 14.6667Z"
                                                    fill="currentColor" />
                                                <path
                                                    d="M8.47816 3.37534C8.09372 3.3053 7.69857 3.3206 7.32069 3.42017C6.94281 3.51974 6.59144 3.70115 6.29143 3.95155C5.99142 4.20195 5.7501 4.51522 5.58457 4.86921C5.41903 5.22319 5.33332 5.60923 5.3335 6.00001C5.3335 6.17682 5.40373 6.34639 5.52876 6.47142C5.65378 6.59644 5.82335 6.66668 6.00016 6.66668C6.17697 6.66668 6.34654 6.59644 6.47157 6.47142C6.59659 6.34639 6.66683 6.17682 6.66683 6.00001C6.66666 5.80386 6.70977 5.61009 6.79309 5.43252C6.87641 5.25494 6.99788 5.09794 7.14884 4.9727C7.2998 4.84746 7.47654 4.75707 7.66645 4.70797C7.85635 4.65888 8.05475 4.65229 8.2475 4.68868C8.51086 4.7398 8.753 4.86827 8.943 5.05767C9.13299 5.24707 9.26222 5.48881 9.31416 5.75201C9.36663 6.02828 9.3304 6.31406 9.21067 6.5685C9.09093 6.82294 8.89381 7.03301 8.6475 7.16868C8.23961 7.40499 7.90255 7.74635 7.67145 8.15721C7.44034 8.56807 7.32364 9.03338 7.3335 9.50468V10C7.3335 10.1768 7.40373 10.3464 7.52876 10.4714C7.65378 10.5964 7.82335 10.6667 8.00016 10.6667C8.17697 10.6667 8.34654 10.5964 8.47157 10.4714C8.59659 10.3464 8.66683 10.1768 8.66683 10V9.50468C8.65846 9.27269 8.71137 9.04258 8.82021 8.83754C8.92905 8.63249 9.08999 8.45974 9.28683 8.33668C9.76984 8.07139 10.1588 7.66298 10.4002 7.16761C10.6416 6.67225 10.7237 6.11425 10.635 5.57036C10.5464 5.02647 10.2914 4.5234 9.90516 4.13034C9.51893 3.73728 9.02041 3.47352 8.47816 3.37534Z"
                                                    fill="currentColor" />
                                                <path
                                                    d="M8.66683 12C8.66683 11.6318 8.36835 11.3333 8.00016 11.3333C7.63197 11.3333 7.3335 11.6318 7.3335 12C7.3335 12.3682 7.63197 12.6666 8.00016 12.6666C8.36835 12.6666 8.66683 12.3682 8.66683 12Z"
                                                    fill="currentColor" />
                                            </g>
                                            <defs>
                                                <clipPath id="clip0_10137_36912">
                                                    <rect width="16" height="16" fill="white" />
                                                </clipPath>
                                            </defs>
                                        </svg>
                                    </div>
                                    <span class="opacity--80">
                                        {{ translate('how_it_work') }}
                                    </span>
                                </div>
                                <button type="{{ env('APP_MODE') != 'demo' ? 'submit' : 'button' }}"
                                        class="btn btn--primary px-5 {{ env('APP_MODE') != 'demo' ? '' : 'call-demo' }}"
                                        >{{ translate('submit') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        @php($snapchatTag = $analyticsData['snapchat_tag'] ?? null)
                        <form
                            action="{{ env('APP_MODE') != 'demo' ? route('admin.business-settings.analytics-update') : 'javascript:' }}"
                            method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="d-flex justify-content-between gap-2 mb-5">
                                <h4 class="mb-0 d-flex gap-1 title-color fz-16">
                                    {{ translate('Snapchat_Pixel') }}
                                </h4>
                                <div class="toggle-switch-with-text">
                                    <label class="switch--custom-label toggle-switch toggle-switch-sm d-inline-flex">
                                        <input type="checkbox" name="is_active" value="1"
                                               class="toggle-switch-input" {{ $snapchatTag?->is_active == 1 ? 'checked' : '' }}>

                                        <span class="toggle-switch-label text">
                                            <span
                                                class="toggle-switch-indicator d-flex align-items-center justify-content-center">
                                                <span>
                                                </span>
                                            </span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="title-color d-flex font-weight-medium">{{ translate('Snap_Pixel_ID') }}</label>
                                <input type="hidden" name="type" value="snapchat_tag">
                                <textarea type="text" placeholder="{{ translate('Enter_the_Snap_Pixel_ID') }}"
                                          class="form-control __h-45px" name="script_id">{!! $snapchatTag?->script_id ?? '' !!}</textarea>
                            </div>
                            <div class="d-flex gap-3 justify-content-between align-items-center">
                                <div
                                    class="d-flex align-items-center gap-2 title-color font-weight-medium text-capitalize cursor-pointer" data-toggle="modal"
                                    data-target="#modalForSnapchatPixel">
                                    <div class="ripple-animation">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                             viewBox="0 0 16 16" fill="none">
                                            <g clip-path="url(#clip0_10137_36912)">
                                                <path
                                                    d="M8 0C6.41775 0 4.87104 0.469192 3.55544 1.34824C2.23985 2.22729 1.21447 3.47672 0.608967 4.93853C0.00346629 6.40034 -0.15496 8.00887 0.153721 9.56072C0.462403 11.1126 1.22433 12.538 2.34315 13.6569C3.46197 14.7757 4.88743 15.5376 6.43928 15.8463C7.99113 16.155 9.59966 15.9965 11.0615 15.391C12.5233 14.7855 13.7727 13.7602 14.6518 12.4446C15.5308 11.129 16 9.58225 16 8C15.9977 5.87897 15.1541 3.84547 13.6543 2.34568C12.1545 0.845886 10.121 0.00229405 8 0ZM8 14.6667C6.68146 14.6667 5.39253 14.2757 4.2962 13.5431C3.19987 12.8106 2.34539 11.7694 1.84081 10.5512C1.33622 9.33305 1.2042 7.99261 1.46144 6.6994C1.71867 5.40619 2.35361 4.21831 3.28596 3.28596C4.21831 2.35361 5.4062 1.71867 6.6994 1.46143C7.99261 1.2042 9.33305 1.33622 10.5512 1.8408C11.7694 2.34539 12.8106 3.19987 13.5431 4.2962C14.2757 5.39253 14.6667 6.68146 14.6667 8C14.6647 9.76752 13.9617 11.4621 12.7119 12.7119C11.4621 13.9617 9.76752 14.6647 8 14.6667Z"
                                                    fill="currentColor" />
                                                <path
                                                    d="M8.47816 3.37534C8.09372 3.3053 7.69857 3.3206 7.32069 3.42017C6.94281 3.51974 6.59144 3.70115 6.29143 3.95155C5.99142 4.20195 5.7501 4.51522 5.58457 4.86921C5.41903 5.22319 5.33332 5.60923 5.3335 6.00001C5.3335 6.17682 5.40373 6.34639 5.52876 6.47142C5.65378 6.59644 5.82335 6.66668 6.00016 6.66668C6.17697 6.66668 6.34654 6.59644 6.47157 6.47142C6.59659 6.34639 6.66683 6.17682 6.66683 6.00001C6.66666 5.80386 6.70977 5.61009 6.79309 5.43252C6.87641 5.25494 6.99788 5.09794 7.14884 4.9727C7.2998 4.84746 7.47654 4.75707 7.66645 4.70797C7.85635 4.65888 8.05475 4.65229 8.2475 4.68868C8.51086 4.7398 8.753 4.86827 8.943 5.05767C9.13299 5.24707 9.26222 5.48881 9.31416 5.75201C9.36663 6.02828 9.3304 6.31406 9.21067 6.5685C9.09093 6.82294 8.89381 7.03301 8.6475 7.16868C8.23961 7.40499 7.90255 7.74635 7.67145 8.15721C7.44034 8.56807 7.32364 9.03338 7.3335 9.50468V10C7.3335 10.1768 7.40373 10.3464 7.52876 10.4714C7.65378 10.5964 7.82335 10.6667 8.00016 10.6667C8.17697 10.6667 8.34654 10.5964 8.47157 10.4714C8.59659 10.3464 8.66683 10.1768 8.66683 10V9.50468C8.65846 9.27269 8.71137 9.04258 8.82021 8.83754C8.92905 8.63249 9.08999 8.45974 9.28683 8.33668C9.76984 8.07139 10.1588 7.66298 10.4002 7.16761C10.6416 6.67225 10.7237 6.11425 10.635 5.57036C10.5464 5.02647 10.2914 4.5234 9.90516 4.13034C9.51893 3.73728 9.02041 3.47352 8.47816 3.37534Z"
                                                    fill="currentColor" />
                                                <path
                                                    d="M8.66683 12C8.66683 11.6318 8.36835 11.3333 8.00016 11.3333C7.63197 11.3333 7.3335 11.6318 7.3335 12C7.3335 12.3682 7.63197 12.6666 8.00016 12.6666C8.36835 12.6666 8.66683 12.3682 8.66683 12Z"
                                                    fill="currentColor" />
                                            </g>
                                            <defs>
                                                <clipPath id="clip0_10137_36912">
                                                    <rect width="16" height="16" fill="white" />
                                                </clipPath>
                                            </defs>
                                        </svg>
                                    </div>
                                    <span class="opacity--80">
                                        {{ translate('how_it_work') }}
                                    </span>
                                </div>

                                <button type="{{ env('APP_MODE') != 'demo' ? 'submit' : 'button' }}"
                                        class="btn btn--primary px-5 {{ env('APP_MODE') != 'demo' ? '' : 'call-demo' }}"
                                        >{{ translate('submit') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        @php($tiktokTag = $analyticsData['tiktok_tag'] ?? null)
                        <form
                            action="{{ env('APP_MODE') != 'demo' ? route('admin.business-settings.analytics-update') : 'javascript:' }}"
                            method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="d-flex justify-content-between gap-2 mb-5">
                                <h4 class="mb-0 d-flex gap-1 title-color fz-16">
                                    {{ translate('TikTok_Pixel') }}
                                </h4>
                                <div class="toggle-switch-with-text">
                                    <label class="switch--custom-label toggle-switch toggle-switch-sm d-inline-flex">
                                        <input type="checkbox" name="is_active" value="1"
                                               class="toggle-switch-input" {{ $tiktokTag?->is_active == 1 ? 'checked' : '' }}>

                                        <span class="toggle-switch-label text">
                                            <span
                                                class="toggle-switch-indicator d-flex align-items-center justify-content-center">
                                                <span>
                                                </span>
                                            </span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="title-color d-flex font-weight-medium">{{ translate('TikTok_Pixel_ID') }}</label>
                                <input type="hidden" name="type" value="tiktok_tag">
                                <textarea type="text" placeholder="{{ translate('Enter_the_TikTok_Pixel_ID') }}"
                                          class="form-control __h-45px" name="script_id">{!! $tiktokTag?->script_id ?? '' !!}</textarea>
                            </div>
                            <div class="d-flex gap-3 justify-content-between align-items-center">
                                <div
                                    class="d-flex align-items-center gap-2 title-color font-weight-medium text-capitalize cursor-pointer" data-toggle="modal"
                                    data-target="#modalForTikTokPixel">
                                    <div class="ripple-animation">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                             viewBox="0 0 16 16" fill="none">
                                            <g clip-path="url(#clip0_10137_36912)">
                                                <path
                                                    d="M8 0C6.41775 0 4.87104 0.469192 3.55544 1.34824C2.23985 2.22729 1.21447 3.47672 0.608967 4.93853C0.00346629 6.40034 -0.15496 8.00887 0.153721 9.56072C0.462403 11.1126 1.22433 12.538 2.34315 13.6569C3.46197 14.7757 4.88743 15.5376 6.43928 15.8463C7.99113 16.155 9.59966 15.9965 11.0615 15.391C12.5233 14.7855 13.7727 13.7602 14.6518 12.4446C15.5308 11.129 16 9.58225 16 8C15.9977 5.87897 15.1541 3.84547 13.6543 2.34568C12.1545 0.845886 10.121 0.00229405 8 0ZM8 14.6667C6.68146 14.6667 5.39253 14.2757 4.2962 13.5431C3.19987 12.8106 2.34539 11.7694 1.84081 10.5512C1.33622 9.33305 1.2042 7.99261 1.46144 6.6994C1.71867 5.40619 2.35361 4.21831 3.28596 3.28596C4.21831 2.35361 5.4062 1.71867 6.6994 1.46143C7.99261 1.2042 9.33305 1.33622 10.5512 1.8408C11.7694 2.34539 12.8106 3.19987 13.5431 4.2962C14.2757 5.39253 14.6667 6.68146 14.6667 8C14.6647 9.76752 13.9617 11.4621 12.7119 12.7119C11.4621 13.9617 9.76752 14.6647 8 14.6667Z"
                                                    fill="currentColor" />
                                                <path
                                                    d="M8.47816 3.37534C8.09372 3.3053 7.69857 3.3206 7.32069 3.42017C6.94281 3.51974 6.59144 3.70115 6.29143 3.95155C5.99142 4.20195 5.7501 4.51522 5.58457 4.86921C5.41903 5.22319 5.33332 5.60923 5.3335 6.00001C5.3335 6.17682 5.40373 6.34639 5.52876 6.47142C5.65378 6.59644 5.82335 6.66668 6.00016 6.66668C6.17697 6.66668 6.34654 6.59644 6.47157 6.47142C6.59659 6.34639 6.66683 6.17682 6.66683 6.00001C6.66666 5.80386 6.70977 5.61009 6.79309 5.43252C6.87641 5.25494 6.99788 5.09794 7.14884 4.9727C7.2998 4.84746 7.47654 4.75707 7.66645 4.70797C7.85635 4.65888 8.05475 4.65229 8.2475 4.68868C8.51086 4.7398 8.753 4.86827 8.943 5.05767C9.13299 5.24707 9.26222 5.48881 9.31416 5.75201C9.36663 6.02828 9.3304 6.31406 9.21067 6.5685C9.09093 6.82294 8.89381 7.03301 8.6475 7.16868C8.23961 7.40499 7.90255 7.74635 7.67145 8.15721C7.44034 8.56807 7.32364 9.03338 7.3335 9.50468V10C7.3335 10.1768 7.40373 10.3464 7.52876 10.4714C7.65378 10.5964 7.82335 10.6667 8.00016 10.6667C8.17697 10.6667 8.34654 10.5964 8.47157 10.4714C8.59659 10.3464 8.66683 10.1768 8.66683 10V9.50468C8.65846 9.27269 8.71137 9.04258 8.82021 8.83754C8.92905 8.63249 9.08999 8.45974 9.28683 8.33668C9.76984 8.07139 10.1588 7.66298 10.4002 7.16761C10.6416 6.67225 10.7237 6.11425 10.635 5.57036C10.5464 5.02647 10.2914 4.5234 9.90516 4.13034C9.51893 3.73728 9.02041 3.47352 8.47816 3.37534Z"
                                                    fill="currentColor" />
                                                <path
                                                    d="M8.66683 12C8.66683 11.6318 8.36835 11.3333 8.00016 11.3333C7.63197 11.3333 7.3335 11.6318 7.3335 12C7.3335 12.3682 7.63197 12.6666 8.00016 12.6666C8.36835 12.6666 8.66683 12.3682 8.66683 12Z"
                                                    fill="currentColor" />
                                            </g>
                                            <defs>
                                                <clipPath id="clip0_10137_36912">
                                                    <rect width="16" height="16" fill="white" />
                                                </clipPath>
                                            </defs>
                                        </svg>
                                    </div>
                                    <span class="opacity--80">
                                        {{ translate('how_it_work') }}
                                    </span>
                                </div>
                                <button type="{{ env('APP_MODE') != 'demo' ? 'submit' : 'button' }}"
                                        class="btn btn--primary px-5 {{ env('APP_MODE') != 'demo' ? '' : 'call-demo' }}"
                                        >{{ translate('submit') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        @php($twitterTag = $analyticsData['twitter_tag'] ?? null)
                        <form
                            action="{{ env('APP_MODE') != 'demo' ? route('admin.business-settings.analytics-update') : 'javascript:' }}"
                            method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="d-flex justify-content-between gap-2 mb-5">
                                <h4 class="mb-0 d-flex gap-1 title-color fz-16">
                                    {{ translate('X') }} ({{ translate('Twitter') }}) {{ translate('Pixel') }}
                                </h4>
                                <div class="toggle-switch-with-text">
                                    <label class="switch--custom-label toggle-switch toggle-switch-sm d-inline-flex">
                                        <input type="checkbox" name="is_active" value="1"
                                               class="toggle-switch-input" {{ $twitterTag?->is_active == 1 ? 'checked' : '' }}>

                                        <span class="toggle-switch-label text">
                                            <span
                                                class="toggle-switch-indicator d-flex align-items-center justify-content-center">
                                                <span>
                                                </span>
                                            </span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="title-color d-flex font-weight-medium">{{ translate('Pixel_ID') }}</label>
                                <input type="hidden" name="type" value="twitter_tag">
                                <textarea type="text" placeholder="{{ translate('Enter_the_Pixel_ID') }}"
                                          class="form-control __h-45px" name="script_id">{!! $twitterTag?->script_id ?? '' !!}</textarea>
                            </div>
                            <div class="d-flex gap-3 justify-content-between align-items-center">
                                <div
                                    class="d-flex align-items-center gap-2 title-color font-weight-medium text-capitalize cursor-pointer" data-toggle="modal"
                                    data-target="#modalForTwitterPixel">
                                    <div class="ripple-animation">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                             viewBox="0 0 16 16" fill="none">
                                            <g clip-path="url(#clip0_10137_36912)">
                                                <path
                                                    d="M8 0C6.41775 0 4.87104 0.469192 3.55544 1.34824C2.23985 2.22729 1.21447 3.47672 0.608967 4.93853C0.00346629 6.40034 -0.15496 8.00887 0.153721 9.56072C0.462403 11.1126 1.22433 12.538 2.34315 13.6569C3.46197 14.7757 4.88743 15.5376 6.43928 15.8463C7.99113 16.155 9.59966 15.9965 11.0615 15.391C12.5233 14.7855 13.7727 13.7602 14.6518 12.4446C15.5308 11.129 16 9.58225 16 8C15.9977 5.87897 15.1541 3.84547 13.6543 2.34568C12.1545 0.845886 10.121 0.00229405 8 0ZM8 14.6667C6.68146 14.6667 5.39253 14.2757 4.2962 13.5431C3.19987 12.8106 2.34539 11.7694 1.84081 10.5512C1.33622 9.33305 1.2042 7.99261 1.46144 6.6994C1.71867 5.40619 2.35361 4.21831 3.28596 3.28596C4.21831 2.35361 5.4062 1.71867 6.6994 1.46143C7.99261 1.2042 9.33305 1.33622 10.5512 1.8408C11.7694 2.34539 12.8106 3.19987 13.5431 4.2962C14.2757 5.39253 14.6667 6.68146 14.6667 8C14.6647 9.76752 13.9617 11.4621 12.7119 12.7119C11.4621 13.9617 9.76752 14.6647 8 14.6667Z"
                                                    fill="currentColor" />
                                                <path
                                                    d="M8.47816 3.37534C8.09372 3.3053 7.69857 3.3206 7.32069 3.42017C6.94281 3.51974 6.59144 3.70115 6.29143 3.95155C5.99142 4.20195 5.7501 4.51522 5.58457 4.86921C5.41903 5.22319 5.33332 5.60923 5.3335 6.00001C5.3335 6.17682 5.40373 6.34639 5.52876 6.47142C5.65378 6.59644 5.82335 6.66668 6.00016 6.66668C6.17697 6.66668 6.34654 6.59644 6.47157 6.47142C6.59659 6.34639 6.66683 6.17682 6.66683 6.00001C6.66666 5.80386 6.70977 5.61009 6.79309 5.43252C6.87641 5.25494 6.99788 5.09794 7.14884 4.9727C7.2998 4.84746 7.47654 4.75707 7.66645 4.70797C7.85635 4.65888 8.05475 4.65229 8.2475 4.68868C8.51086 4.7398 8.753 4.86827 8.943 5.05767C9.13299 5.24707 9.26222 5.48881 9.31416 5.75201C9.36663 6.02828 9.3304 6.31406 9.21067 6.5685C9.09093 6.82294 8.89381 7.03301 8.6475 7.16868C8.23961 7.40499 7.90255 7.74635 7.67145 8.15721C7.44034 8.56807 7.32364 9.03338 7.3335 9.50468V10C7.3335 10.1768 7.40373 10.3464 7.52876 10.4714C7.65378 10.5964 7.82335 10.6667 8.00016 10.6667C8.17697 10.6667 8.34654 10.5964 8.47157 10.4714C8.59659 10.3464 8.66683 10.1768 8.66683 10V9.50468C8.65846 9.27269 8.71137 9.04258 8.82021 8.83754C8.92905 8.63249 9.08999 8.45974 9.28683 8.33668C9.76984 8.07139 10.1588 7.66298 10.4002 7.16761C10.6416 6.67225 10.7237 6.11425 10.635 5.57036C10.5464 5.02647 10.2914 4.5234 9.90516 4.13034C9.51893 3.73728 9.02041 3.47352 8.47816 3.37534Z"
                                                    fill="currentColor" />
                                                <path
                                                    d="M8.66683 12C8.66683 11.6318 8.36835 11.3333 8.00016 11.3333C7.63197 11.3333 7.3335 11.6318 7.3335 12C7.3335 12.3682 7.63197 12.6666 8.00016 12.6666C8.36835 12.6666 8.66683 12.3682 8.66683 12Z"
                                                    fill="currentColor" />
                                            </g>
                                            <defs>
                                                <clipPath id="clip0_10137_36912">
                                                    <rect width="16" height="16" fill="white" />
                                                </clipPath>
                                            </defs>
                                        </svg>
                                    </div>
                                    <span class="opacity--80">
                                        {{ translate('how_it_work') }}
                                    </span>
                                </div>
                                <button type="{{ env('APP_MODE') != 'demo' ? 'submit' : 'button' }}"
                                        class="btn btn--primary px-5 {{ env('APP_MODE') != 'demo' ? '' : 'call-demo' }}"
                                        >{{ translate('submit') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include("admin-views.business-settings.analytics._information-modal")
    @include("admin-views.business-settings.analytics._google-analytics-modal")
    @include("admin-views.business-settings.analytics._google-tag-manager-modal")
    @include("admin-views.business-settings.analytics._linkedin-insight-modal")
    @include("admin-views.business-settings.analytics._facebook-meta-pixel-modal")
    @include("admin-views.business-settings.analytics._pinterest-tag-modal")
    @include("admin-views.business-settings.analytics._snapchat-tag-modal")
    @include("admin-views.business-settings.analytics._tiktok-tag-modal")
    @include("admin-views.business-settings.analytics._twitter-modal")
@endsection

@push('script')
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/vendor/swiper/swiper-bundle.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleSwitches = document.querySelectorAll('.toggle-switch-with-text .toggle-switch-input');
            toggleSwitches.forEach(function(toggleSwitch) {
                const indicator = toggleSwitch.closest('label').querySelector(
                    '.toggle-switch-with-text .toggle-switch-indicator span');

                function updateIndicator() {
                    indicator.textContent = toggleSwitch.checked ? '{{ translate('On') }}' : '{{ translate('Off') }}';
                }

                updateIndicator();

                toggleSwitch.addEventListener('change', updateIndicator);
            });
        });
    </script>
@endpush
