@extends(isset($blogPlatform) && $blogPlatform == 'app' ? 'theme-views.blogs.blog-layouts' : 'theme-views.layouts.app')

@section('title', translate('Popular_Blogs'))

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
                        {{ translate('popular_blog') }}
                    </h1>
                </div>
            </div>
            <div class="d-block d-sm-none">
                <h2 class="fs-16 fw-semibold my-3 text-center">{{ translate('popular_blog') }}</h2>
            </div>

            <div class="d-flex flex-column align-items-center justify-content-center">
                <div class="flex-grow-1 mb-4 blog-banner-search">
                    <form action="{{ isset($blogPlatform) && $blogPlatform == 'app' ? route('app.blog.popular-blog', ['locale' => request('locale'), 'theme' => request('theme')]) : route('frontend.blog.popular-blog') }}" id="popular-search-form" method="get">
                        <input type="hidden" name="locale" value="{{ request('locale') }}">
                        <input type="hidden" name="theme" value="{{ request('theme') }}">
                        <div class="input-group-overlay input-group-sm position-relative">
                            <input type="hidden" name="category" value="{{ request('category') }}">
                            <input placeholder="{{ translate('Search_Blog') }}" name="search" id="popular-search"
                                   class="__inline-38 cz-filter-search form-control form-control-sm appended-form-control h-45px bg-absolute-white"
                                   type="text" value="{{ request('search') }}" required>
                            <button type="submit" class="input-group-append-overlay p-0 shadow-none bg-transparent border-0 d-inline-block blog-search-btn">
                                <i class="bi bi-search fs-14"></i>
                            </button>
                        </div>
                    </form>
                    @if(request('search'))
                        <div class="my-2 d-flex gap-2 align-items-baseline justify-content-end clear-all-search-popular cursor-pointer">
                            <h6>{{ translate('Clear_Search') }}</h6>
                            <button type="button" class="btn text-primary border-0 fs-14 fw-bold lh-1 m-0 p-0"><i class="bi bi-x-lg fw-bold"></i></button>
                        </div>
                    @endif
                </div>
            </div>

            <div class="row g-4 sticky-top-nav-search">
                <div class="col-lg-12 order-1 order-lg-0">
                    <div class="position-relative">
                        <ul class="blog-top-nav d-flex gap-3 pt-0">
                            <li class="{{ request('category') == '' ? 'active' : ''}}">
                                <a href="{{ isset($blogPlatform) && $blogPlatform == 'app' ? route('app.blog.popular-blog', ['locale' => request('locale'), 'theme' => request('theme')]) : route('frontend.blog.popular-blog') }}" class="border border-primary-light rounded-10 px-3 py-2">
                                    <span class="opacity-60">{{ translate('All') }}</span>
                                </a>
                            </li>
                            @foreach($blogCategoryList as $blogCategory)
                                <li class="{{ request('category') == $blogCategory?->name ? 'active' : ''}}">
                                    @if(isset($blogPlatform) && $blogPlatform == 'app')
                                        <a href="{{ route('app.blog.popular-blog', ['category' => $blogCategory?->name, 'locale' => request('locale'), 'theme' => request('theme')]) }}" class="border border-primary-light rounded-10 px-3 py-2">
                                            <span class="opacity-60">{{ Str::limit($blogCategory->name, 25) }}</span>
                                        </a>
                                    @else
                                        <a href="{{ route('frontend.blog.popular-blog', ['category' => $blogCategory?->name]) }}" class="border border-primary-light rounded-10 px-3 py-2">
                                            <span class="opacity-60">{{ Str::limit($blogCategory->name, 25) }}</span>
                                        </a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                        <div class="previous-button blog-top-nav_prev-btn">
                            <button class="btn rounded-circle aspect-1">
                                <i class="text-absolute-white bi bi-chevron-left"></i>
                            </button>
                        </div>

                        <div class="next-button blog-top-nav_next-btn">
                            <button class="btn rounded-circle aspect-1">
                                <i class="text-absolute-white bi bi-chevron-right"></i>
                            </button>
                        </div>
                    </div>

                    @if(request('search'))
                        <div class="my-2">
                            <span class="fw-semibold">{{ $popularBlogList->total() }}</span> {{ translate('Search_Result_Found') }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="row g-4">
                @if($popularBlogList->total() <= 0 && request()->has('search'))
                    @include('theme-views.blogs.partials._no-result-found')
                @elseif($popularBlogList->total() <= 0)
                    <div class="row g-4 mb-5">
                        <?php
                            $downloadAppStatus = getWebConfig(name: 'blog_feature_download_app_status') ?? 0;
                            $appTitleData = getWebConfig(name: 'blog_feature_download_app_title') ?? [];
                        ?>

                        <div class="{{ $appTitleData && $downloadAppStatus ? 'col-lg-8' : 'col-lg-12' }}">
                            @include('theme-views.blogs.partials._no-blog-found')
                        </div>

                        @if($appTitleData && $downloadAppStatus)
                            <div class="col-lg-4">
                                @include('theme-views.blogs.partials._download-app-card')
                            </div>
                        @endif
                    </div>
                @else

                @endif
                <div class="col-lg-12">
                    <div class="row g-3 mb-5">
                        @php($blogListIndex = 0)
                        @foreach($popularBlogList as $blogItem)
                            <div class="col-md-4">
                                @include('theme-views.blogs.partials._single-blog-card', ['blogItem' => $blogItem])
                                @php($blogListIndex++)
                            </div>
                        @endforeach

                        @if(count($popularBlogList) > 0)
                            <div class="col-12">
                                <div class="d-flex justify-content-center">
                                    {!! $popularBlogList->links() !!}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ theme_asset(path: 'assets/js/blog.js') }}"></script>
@endpush

