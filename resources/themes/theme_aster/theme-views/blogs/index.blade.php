@extends(isset($blogPlatform) && $blogPlatform == 'app' ? 'theme-views.blogs.blog-layouts' : 'theme-views.layouts.app')

@section('title', translate('Blogs'))

@push('css_or_js')
    @if(isset($blogPlatform) && $blogPlatform == 'app')
        <link rel="stylesheet" href="{{ theme_asset('assets/css/app-blog.css') }}"/>
    @endif
@endpush

@section('content')
    @include('theme-views.blogs.partials._app-blog-preloader')

    <div class="blog-root-container" data-platform="{{ isset($blogPlatform) && $blogPlatform == 'app' ? 'app' : 'web' }}" data-theme="{{ request('theme', 'light') }}">
        <div class="container">
            <div class="rounded-10 my-4 text-center d-none d-sm-block position-relative blog-banner-container">
                <div class="text--primary w-100 position-absolute">
                    <img class="blog-banner-svg svg" src="{{theme_asset(path: 'assets/img/blogs/background.svg')}}" alt="">
                </div>
                <div class="py-5 px-3">
                    <h1 class="mb-2 fw-semibold h2">
                        {{ $blogTitle != '' ? $blogTitle : translate('Blog') }}
                    </h1>
                    @if($blogSubTitle)
                        <p class="fs-18 mb-0">
                            {{ $blogSubTitle }}
                        </p>
                    @endif
                </div>
            </div>
            <div class="d-block d-sm-none">
                <h2 class="fs-16 fw-semibold my-3 text-center">
                    {{ $blogTitle != '' ? $blogTitle : translate('Blog') }}
                </h2>
            </div>

            @if($blogList->total() > 0 || (request()->has('search') || request()->has('category') || request()->has('write')))
            <div class="row g-4 sticky-top-nav-search">
                <div class="col-lg-8 order-1 order-lg-0">
                    <div class="position-relative">
                        <ul class="blog-top-nav d-flex gap-3 pt-0 padding-block-0">
                            <li class="{{ request('category') == '' ? 'active' : ''}}">
                                <a href="{{ isset($blogPlatform) && $blogPlatform == 'app' ? route('app.blog.index', ['locale' => request('locale'), 'theme' => request('theme')]) : route('frontend.blog.index') }}" class="border border-primary-light rounded-10 px-3 py-2">
                                    <span class="opacity-60">{{ translate('All') }}</span>
                                </a>
                            </li>
                            @foreach($blogCategoryList as $blogCategory)
                                <li class="{{ request('category') == $blogCategory?->name ? 'active' : ''}}">
                                    @if(isset($blogPlatform) && $blogPlatform == 'app')
                                        <a href="{{ route('app.blog.index', ['category' => $blogCategory?->name, 'locale' => request('locale'), 'theme' => request('theme')]) }}" class="border border-primary-light rounded-10 px-3 py-2">
                                            <span class="opacity-60">{{ Str::limit($blogCategory->name,25) }}</span>
                                        </a>
                                    @else
                                        <a href="{{ route('frontend.blog.index', ['category' => $blogCategory?->name]) }}" class="border border-primary-light rounded-10 px-3 py-2">
                                            <span class="opacity-60">{{ Str::limit($blogCategory->name,25) }}</span>
                                        </a>
                                    @endif

                                </li>
                            @endforeach
                        </ul>
                        <div class=" blog-top-nav_prev-btn align-items-center">
                            <div class="previous-button">
                                <button class="btn rounded-circle aspect-1">
                                    <i class="text-absolute-white bi bi-chevron-left"></i>
                                </button>
                            </div>

                        </div>

                        <div class="blog-top-nav_next-btn align-items-center">
                                <div class="next-button d-flex justify-content-end">
                                    <button class="btn rounded-circle aspect-1">
                                        <i class="text-absolute-white bi bi-chevron-right"></i>
                                    </button>
                                </div>
                        </div>
                    </div>
                    @if(request('search'))
                        <div class="my-2">
                            <span class="fw-semibold">{{ $blogList->total() }}</span> {{ translate('Search_Result_Found') }}
                        </div>
                    @endif
                </div>
                <div class="col-lg-4">
                    <div class="">
                        <form action="{{ isset($blogPlatform) && $blogPlatform == 'app' ? route('app.blog.index', ['locale' => request('locale'), 'theme' => request('theme')]) : route('frontend.blog.index') }}" method="GET" id="search-form">
                            <input type="hidden" name="locale" value="{{ request('locale') }}">
                            <input type="hidden" name="theme" value="{{ request('theme') }}">
                            <div class="input-group-overlay input-group-sm position-relative">
                                <input type="hidden" name="category" value="{{ request('category') }}">
                                <input class="__inline-38 cz-filter-search form-control form-control-sm appended-form-control h-45px"
                                       placeholder="{{ translate('Search_Blog') }}" type="text" value="{{ request('search') }}" name="search" id="search" required>
                                <button type="submit" class="input-group-append-overlay p-0 shadow-none bg-transparent border-0 d-inline-block blog-search-btn">
                                    <i class="bi bi-search fs-14"></i>
                                </button>
                            </div>
                        </form>
                        @if(request('search'))
                            <div class="my-2 d-flex gap-2 align-items-baseline justify-content-end clear-all-search cursor-pointer">
                                <h6>{{ translate('Clear_Search') }}</h6>
                                <button type="button" class="btn text-primary border-0 fs-14 fw-bold lh-1 m-0 p-0"><i class="bi bi-x-lg fw-bold"></i></button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <div class="row g-4 mb-3">
                @if($blogList->total() <= 0 && !empty(request('search')))
                    <div class="col-12">
                        @include('theme-views.blogs.partials._no-result-found')
                    </div>
                @elseif($blogList->total() <= 0)
                    <?php
                        $downloadAppStatus = getWebConfig(name: 'blog_feature_download_app_status') ?? 0;
                        $appTitleData = getWebConfig(name: 'blog_feature_download_app_title') ?? [];
                    ?>
                    <div class="col-lg-8">
                        @include('theme-views.blogs.partials._no-blog-found')
                    </div>
                    <div class="col-lg-4">
                        <div class="sticky-top-wrapper top-170px">
                            <div class="card mb-4">
                                <div class="card-body p-3">
                                    <h5 class="mb-3">{{ translate('recent_posts') }}</h5>
                                    <div class="recent-post-wrapper">
                                        @php($recentBlogIndex = 0)
                                        @foreach($recentBlogList as $blogItem)
                                            @if ($recentBlogIndex < 6)
                                                <div class="recent-post">
                                                    <div class="d-flex gap-3">
                                                        <img class="h-80px aspect-1 object-fit-cover rounded-10" src="{{ getStorageImages(path: $blogItem?->thumbnail_full_url, type:'wide-banner') }}" alt="{{ $blogItem?->title }}">
                                                        <div class="fs-14 d-flex flex-column">
                                                            <h6 class="line-limit-2 mb-0">
                                                                @if(isset($blogPlatform) && $blogPlatform == 'app')
                                                                    <a href="{{ route('app.blog.details', ['slug' => $blogItem?->slug, 'locale' => request('locale'), 'theme' => request('theme')]) }}">
                                                                        {{ $blogItem?->title }}
                                                                    </a>
                                                                @else
                                                                    <a href="{{ route('frontend.blog.details', ['slug' => $blogItem?->slug]) }}">
                                                                        {{ $blogItem?->title }}
                                                                    </a>
                                                                @endif
                                                            </h6>
                                                            <p class="mb-0">{{ $blogItem->publish_date->diffForHumans() }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @if($appTitleData && $downloadAppStatus)
                                <div>
                                    @include('theme-views.blogs.partials._download-app-card')
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="col-lg-8">
                        <div class="row g-4 mb-0 mb-lg-5">
                            @php($blogListIndex = 0)
                            @foreach($blogList as $blogItem)
                                <div class="{{ $blogListIndex == 0 ? 'col-lg-12' : 'col-md-6' }}">
                                    @include('theme-views.blogs.partials._single-blog-card', ['blogItem' => $blogItem])
                                    @php($blogListIndex++)
                                </div>
                            @endforeach
                        </div>
                        @if(count($blogList) > 0)
                                <div class="col-12">
                                    <div class="d-flex justify-content-start">
                                        {!! $blogList->links() !!}
                                    </div>
                                </div>
                        @endif
                    </div>
                    <div class="col-lg-4">
                        <div class="sticky-top-wrapper top-170px">
                            <div class="card mb-4">
                                <div class="card-body p-3">
                                    <h5 class="mb-3">{{ translate('recent_posts') }}</h5>
                                    <div class="recent-post-wrapper">
                                        @php($recentBlogIndex = 0)
                                        @foreach($recentBlogList as $blogItem)
                                            @if ($recentBlogIndex < 6)
                                                <div class="recent-post">
                                                    <div class="d-flex gap-3">
                                                        <img class="h-80px aspect-1 object-fit-cover rounded-10" src="{{ getStorageImages(path: $blogItem?->thumbnail_full_url, type:'wide-banner') }}" alt="{{ $blogItem?->title }}">
                                                        <div class="fs-14 d-flex flex-column">
                                                            <h6 class="line-limit-2 mb-0">
                                                                @if(isset($blogPlatform) && $blogPlatform == 'app')
                                                                    <a href="{{ route('app.blog.details', ['slug' => $blogItem?->slug, 'locale' => request('locale'), 'theme' => request('theme')]) }}">
                                                                        {{ $blogItem?->title }}
                                                                    </a>
                                                                @else
                                                                    <a href="{{ route('frontend.blog.details', ['slug' => $blogItem?->slug]) }}">
                                                                        {{ $blogItem?->title }}
                                                                    </a>
                                                                @endif
                                                            </h6>
                                                            <p class="mb-0">{{ $blogItem->publish_date->diffForHumans() }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="pb-5">
                                @include('theme-views.blogs.partials._download-app-card')
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ theme_asset(path: 'assets/js/blog.js') }}"></script>
@endpush
