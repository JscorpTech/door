@extends(isset($blogPlatform) && $blogPlatform == 'app' ? 'web-views.blogs.blog-layouts' : 'layouts.front-end.app')

@section('title', translate('Blogs'))

@push('css_or_js')
    @if(isset($blogPlatform) && $blogPlatform == 'app')
        <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/app-blog.css') }}"/>
    @endif
@endpush

@section('content')
    @include('web-views.blogs.partials._app-blog-preloader')

    <div class="blog-root-container">
        <div class="container">
            <div class="rounded-10 my-4 text-center d-none d-sm-block position-relative blog-banner-container">
                <div class="text--primary w-100 position-absolute">
                    <img class="blog-banner-svg svg" src="{{ theme_asset(path: 'public/assets/front-end/img/blogs/background.svg') }}" alt="">
                </div>
                <div class="py-5 px-3">
                    <h1 class="mb-2 fw-semibold h2">
                        {{ $blogTitle != '' ? $blogTitle : translate('Blog') }}
                    </h1>
                    @if($blogSubTitle)
                        <p class="fs-20 mb-0">
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

            <div class="row g-4">
                @if($blogList->total() > 0 || (request()->has('search') || request()->has('category') || request()->has('write')))
                    <div class="col-lg-8 order-1 order-lg-0">
                        <div class="position-relative">
                            <ul class="blog-top-nav d-flex gap-3">
                                <li class="{{ request('category') == '' ? 'active' : ''}}">
                                    <a href="{{ isset($blogPlatform) && $blogPlatform == 'app' ? route('app.blog.index', ['locale' => request('locale'), 'theme' => request('theme')]) : route('frontend.blog.index') }}"
                                         class="border rounded-10 px-3 py-2">
                                        <span class="opacity-60">{{ translate('all') }}</span>
                                    </a>
                                </li>
                                @foreach($blogCategoryList as $blogCategory)
                                    @if(isset($blogPlatform) && $blogPlatform == 'app')
                                        <li class="{{ request('category') == $blogCategory?->name ? 'active' : ''}}">
                                            <a href="{{ route('app.blog.index', ['category' => $blogCategory?->name, 'locale' => request('locale'), 'theme' => request('theme')]) }}" class="border rounded-10 px-3 py-2">
                                                <span class="opacity-60">{{ Str::limit($blogCategory->name, 25) }}</span>
                                            </a>
                                        </li>
                                    @else
                                        <li class="{{ request('category') == $blogCategory?->name ? 'active' : ''}}">
                                            <a href="{{ route('frontend.blog.index', ['category' => $blogCategory?->name]) }}" class="border rounded-10 px-3 py-2">
                                                <span class="opacity-60">{{ Str::limit($blogCategory->name, 25) }}</span>
                                            </a>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                            <div class="blog-top-nav_prev-btn align-items-center">
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
                            <div class="mt-3">
                                <span class="fw-semibold">{{ $blogList->count() }}</span>
                                <span class="px-1">{{ translate('Search_Result_Found') }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="col-lg-4">
                        <div class="">
                            <form action="{{ isset($blogPlatform) && $blogPlatform == 'app' ? route('app.blog.index', ['locale' => request('locale'), 'theme' => request('theme')]) : route('frontend.blog.index') }}" method="get" id="search-form">
                                <input type="hidden" name="locale" value="{{ request('locale') }}">
                                <input type="hidden" name="theme" value="{{ request('theme') }}">
                                <div class="input-group-overlay input-group-sm">
                                    <input type="hidden" name="category" value="{{ request('category') }}">
                                    <input placeholder="{{ translate('Search_Blog') }}" value="{{ request('search') }}"
                                           class="__inline-38 cz-filter-search form-control form-control-sm appended-form-control h-45px"
                                           type="text" name="search" id="search" required>
                                    <button type="submit" class="input-group-append-overlay p-0 shadow-none bg-transparent border-0 d-inline-block">
                                    <span class="input-group-text p-0 pb-2">
                                        <i class="czi-search"></i>
                                    </span>
                                    </button>
                                </div>
                            </form>
                            @if(request('search'))
                                <div class="mt-3 d-flex gap-2 align-items-baseline justify-content-end clear-all-search cursor-pointer">
                                    <h6>{{ translate('Clear_Search') }}</h6>
                                    <button type="button" class="btn fs-14 fw-bold lh-1 m-0 p-0"><i class="czi-close fw-bold"></i></button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

            </div>

            <div class="row g-lg-4 mb-lg-5">
                @if($blogList->total() <= 0 && !empty(request('search')))
                    <div class="col-12">
                        @include('web-views.blogs.partials._no-result-found')
                    </div>
                @elseif($blogList->total() <= 0)
                        <?php
                            $downloadAppStatus = getWebConfig(name: 'blog_feature_download_app_status') ?? 0;
                            $appTitleData = getWebConfig(name: 'blog_feature_download_app_title') ?? [];
                        ?>
                    <div class="col-lg-8">
                        @include('web-views.blogs.partials._no-blog-found')
                    </div>
                    <div class="col-lg-4">
                        <div class="sticky-top-wrapper">
                            <div class="card mt-3 mt-lg-0 mb-4">
                                <div class="card-body p-3">
                                    <h5 class="mb-3">{{ translate('Recent_Posts') }}</h5>
                                    <div>
                                        @php($recentBlogIndex = 0)
                                        @foreach($recentBlogList as $blogItem)
                                            @if ($recentBlogIndex < 6)
                                                <div class="recent-post">
                                                    <div class="d-flex gap-3 align-items-center">
                                                        <img class="h-80px aspect-1 object-cover rounded-10"
                                                             src="{{ getStorageImages(path: $blogItem?->thumbnail_full_url, type:'wide-banner') }}"
                                                             alt="{{ $blogItem?->title }}">
                                                        <div class="fs-14 d-flex flex-column">
                                                            <h6 class="line--limit-2 mb-1">
                                                                @if(isset($blogPlatform) && $blogPlatform == 'app')
                                                                    <a href="{{ route('app.blog.details', ['slug' => $blogItem?->slug, 'locale' => request('locale'), 'theme' => request('theme')]) }}" class="line--limit-2 fs-14">
                                                                        {{ $blogItem?->title }}
                                                                    </a>
                                                                @else
                                                                    <a href="{{ route('frontend.blog.details', ['slug' => $blogItem?->slug]) }}" class="line--limit-2 fs-14">
                                                                        {{ $blogItem?->title }}
                                                                    </a>
                                                                @endif
                                                            </h6>
                                                            <p class="mb-0 fs-12">
                                                                {{ $blogItem->publish_date->diffForHumans() }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                            @php($recentBlogIndex++)
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @if($appTitleData && $downloadAppStatus)
                                <div>
                                    @include('web-views.blogs.partials._download-app-card')
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="{{ !request('search') ? 'col-lg-8' : 'col-lg-12' }}">
                        <div class="row g-4 mb-0 mb-lg-5">
                            @php($blogListIndex = 0)
                            @foreach($blogList as $blogItem)
                                <div class="{{ !request('search') ? ($blogListIndex == 0 ? ('col-lg-12') : 'col-md-6') : 'col-md-4' }}">
                                    @include('web-views.blogs.partials._single-blog-card', ['blogItem' => $blogItem])
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

                    @if(!request('search'))
                        <div class="col-lg-4">
                            <div class="sticky-top-wrapper">
                                <div class="card mt-3 mt-lg-0 mb-4">
                                    <div class="card-body p-3">
                                        <h5 class="mb-3">{{ translate('Recent_Posts') }}</h5>
                                        <div>
                                            @php($recentBlogIndex = 0)
                                            @foreach($recentBlogList as $blogItem)
                                                @if ($recentBlogIndex < 6)
                                                    <div class="recent-post">
                                                        <div class="d-flex gap-3 align-items-center">
                                                            <img class="h-80px aspect-1 object-cover rounded-10"
                                                                 src="{{ getStorageImages(path: $blogItem?->thumbnail_full_url, type:'wide-banner') }}"
                                                                 alt="{{ $blogItem?->title }}">
                                                            <div class="fs-14 d-flex flex-column">
                                                                <h6 class="line--limit-2 mb-1">
                                                                    @if(isset($blogPlatform) && $blogPlatform == 'app')
                                                                        <a href="{{ route('app.blog.details', ['slug' => $blogItem?->slug, 'locale' => request('locale'), 'theme' => request('theme')]) }}" class="line--limit-2 fs-14">
                                                                            {{ $blogItem?->title }}
                                                                        </a>
                                                                    @else
                                                                        <a href="{{ route('frontend.blog.details', ['slug' => $blogItem?->slug]) }}" class="line--limit-2 fs-14">
                                                                            {{ $blogItem?->title }}
                                                                        </a>
                                                                    @endif
                                                                </h6>
                                                                <p class="mb-0 fs-12">
                                                                    {{ $blogItem->publish_date->diffForHumans() }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                                @php($recentBlogIndex++)
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <div class="pb-5">
                                    @include('web-views.blogs.partials._download-app-card')
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
            </div>

        </div>
    </div>
@endsection

@push('script')
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/blog.js') }}"></script>
@endpush
