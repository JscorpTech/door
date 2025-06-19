@extends(isset($blogPlatform) && $blogPlatform == 'app' ? 'web-views.blogs.blog-layouts' : 'layouts.front-end.app')

@section('title', $blogData['title'] ?? translate('Blog_Details'))

@push('css_or_js')
    @include(VIEW_FILE_NAMES['blog_seo_meta_content_partials'], ['metaContentData' => $blogData?->seoInfo, 'blogDetails' => $blogData])

    @if(isset($blogPlatform) && $blogPlatform == 'app')
        <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/app-blog.css') }}"/>
    @endif
@endpush

@section('content')
    @include('web-views.blogs.partials._app-blog-preloader')

    <div class="blog-root-container" data-platform="{{ isset($blogPlatform) && $blogPlatform == 'app' ? 'app' : 'web' }}">
        <div class="container">
            <div class="d-flex flex-column gap-3">

                <div class="row g-lg-4 justify-content-center mt-3">
                    @if(count($articleLinks) > 0)
                        <div class="col-lg-3">
                            <div class="position-relative">
                                <div class="article-nav-wrapper_collapse ">
                                    <i class="czi-menu open-icon fw-bold"></i>
                                    <i class="czi-close close-icon fw-bold fs-10 d-none"></i>
                                </div>
                            </div>
                            <div class="article-nav-wrapper sticky-top-wrapper sticky-top-blog-details card border-0 p-3 pt-4 pt-lg-3 d-none d-lg-block">
                                <h5 class="fw-semibold mb-4 mb-lg-3 ml-5 ml-lg-0">{{ translate('In_this_article') }}:</h5>
                                <hr class="mt-0 mb-3 d-none d-lg-block">
                                <ul class="m-0 p-0 scrollspy-blog-details-menu">
                                    @foreach ($articleLinks as $link)
                                        @if(!empty($link['text']))
                                            <li class="">
                                                <a href="#{{ $link['id'] }}" class="line-clamp-1">{{ $link['text'] }}</a>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <div class="{{ count($articleLinks) > 0 ? 'col-lg-6 pt-0 pt-sm-2' : 'col-lg-9' }}">

                        <div class="mt-4 mb-3">
                            @if(request('source') == 'draft')
                                <div class="d-flex justify-content-center">
                                    <span class="btn btn-outline-danger border border-danger fs-13 mb-3 px-3 py-2 rounded text-center max-w-500">
                                        <span class="pe-2"><i class="fa fa-question-circle"></i></span>
                                        <span>{{ translate('This_is_a_draft_copy.') }} {{ translate('It_has_not_been_published_yet.') }}</span>
                                    </span>
                                </div>
                            @endif

                            @if($blogData?->category?->name)
                                <div class="text-center">
                                    <span class="badge badge-info rounded px-3 py-2 fs-12 text-uppercase mb-10px text-center">
                                        {{ Str::limit($blogData?->category?->name, 25) }}
                                    </span>
                                </div>
                            @endif

                            <h1 class="fs-24 fw-bold mb-4 text-center fs-16-mobile">
                                {{ $blogData['title'] ?? null }}
                            </h1>
                            <div class="opacity-70 fs-14 fs-12-mobile lh-1 d-flex justify-content-between justify-content-sm-center align-items-center gap-3 mb-0 mb-lg-5">
                                @if(isset($blogPlatform) && $blogPlatform == 'app')
                                    @if($blogData->writer)
                                        <span class="border-inline-end pe-3">
                                            {{ translate('By') }}
                                            <a href="{{ route('app.blog.index', ['writer' => $blogData['writer'], 'locale' => request('locale'), 'theme' => request('theme')]) }}" class="fw-semibold opacity-1"
                                               title="{{ $blogData['writer'] }}">
                                                {{ Str::limit($blogData['writer'], 50, '...') }}
                                            </a>
                                        </span>
                                    @endif
                                @else
                                    @if($blogData->writer)
                                        <span class="border-inline-end pe-3">
                                                {{ translate('By') }}
                                                <a href="{{ route('frontend.blog.index', ['writer' => $blogData['writer']]) }}" class="fw-semibold opacity-1"
                                                   title="{{ $blogData['writer'] }}">
                                                    {{ Str::limit($blogData['writer'] , 50, '...') }}
                                                </a>
                                            </span>
                                    @endif
                                @endif

                                <span class="border-inline-end pe-3">
                                        {{ $blogData['click_count'] ?? 0 }} {{ translate('views') }}
                                    </span>
                                <span>{{ date('M d, Y', strtotime($blogData['publish_date'] ?? null)) }}</span>
                            </div>
                        </div>

                        <div data-bs-spy="scroll" data-bs-target="#simple-list-example" data-bs-offset="0" data-bs-smooth-scroll="true" class="scrollspy-blog-details" tabindex="0">
                            <img class="max-height-420px w-100 aspect-2 rounded-10 mb-4 mb-sm-5"
                                 src="{{ getStorageImages(path: $blogData['thumbnail_full_url'] ?? null, type:'wide-banner') }}"
                                 alt="{{ $blogData['title'] ?? null }}">
                            <div>
                                {!! $updatedDescription !!}
                            </div>
                        </div>
                    </div>
                    <?php
                    $downloadAppStatus = getWebConfig(name: 'blog_feature_download_app_status') ?? 0;
                    $appTitleData = getWebConfig(name: 'blog_feature_download_app_title') ?? [];
                    ?>


                    @if(isset($blogPlatform) && $blogPlatform == 'web' && ($appTitleData && $downloadAppStatus))
                        <div class="col-lg-3 d-none d-lg-block">
                            <div class="sticky-top-wrapper sticky-top-blog-details text-center">
                                <div class="mb-3 text-info">{{ translate('Share_Now') }}</div>
                                <div class="d-flex justify-content-center align-items-center gap-3">

                                    <a href="javascript:" class="share-on-social-media"
                                       data-action="{{ route('frontend.blog.details', ['slug' => $blogData['slug'] ?? null]) }}"
                                       data-social-media-name="facebook.com/sharer/sharer.php?u=">
                                        <img width="30" src="{{theme_asset(path: 'public/assets/front-end/img/blogs/facebook.svg')}}" alt="">
                                    </a>

                                    <a href="javascript:"
                                       class="share-on-social-media"
                                       data-action="{{ route('frontend.blog.details', ['slug' => $blogData['slug'] ?? null]) }}"
                                       data-social-media-name="twitter.com/intent/tweet?text=">
                                        <img width="30" src="{{theme_asset(path: 'public/assets/front-end/img/blogs/twitter.svg')}}" alt="">
                                    </a>

                                    <a href="javascript:"
                                       class="share-on-social-media"
                                       data-action="{{ route('frontend.blog.details', ['slug' =>$blogData['slug'] ?? null]) }}"
                                       data-social-media-name="linkedin.com/shareArticle?mini=true&url=">
                                        <img width="30" src="{{theme_asset(path: 'public/assets/front-end/img/blogs/linkedin.svg')}}" alt="">
                                    </a>

                                    <a href="javascript:"
                                       class="share-on-social-media"
                                       data-action="{{ route('frontend.blog.details', ['slug' => $blogData['slug'] ?? null]) }}"
                                       data-social-media-name="api.whatsapp.com/send?text=">
                                        <img width="30" src="{{theme_asset(path: 'public/assets/front-end/img/blogs/whatsapp.svg')}}" alt="">
                                    </a>

                                </div>
                                <hr class="my-4">

                                @include('web-views.blogs.partials._download-app-card')
                            </div>
                        </div>
                    @endif

                    <div class="col-lg-12 mt-4 mt-lg-0">
                        <div class="text-center mb-50">
                            <h2 class="fs-18 fs-16-mobile">{{ translate('Share_this_article') }}</h2>
                            <div class="d-flex justify-content-center align-items-center gap-3 border-top-before-after">

                                <a href="javascript:" class="share-on-social-media"
                                   data-action="{{ route('frontend.blog.details', ['slug' => $blogData['slug'] ?? null]) }}"
                                   data-social-media-name="facebook.com/sharer/sharer.php?u=">
                                    <img width="30" src="{{theme_asset(path: 'public/assets/front-end/img/blogs/facebook.svg')}}" alt="">
                                </a>

                                <a href="javascript:"
                                   class="share-on-social-media"
                                   data-action="{{ route('frontend.blog.details', ['slug' => $blogData['slug'] ?? null]) }}"
                                   data-social-media-name="twitter.com/intent/tweet?text=">
                                    <img width="30" src="{{theme_asset(path: 'public/assets/front-end/img/blogs/twitter.svg')}}" alt="">
                                </a>

                                <a href="javascript:"
                                   class="share-on-social-media"
                                   data-action="{{ route('frontend.blog.details', ['slug' => $blogData['slug'] ?? null]) }}"
                                   data-social-media-name="linkedin.com/shareArticle?mini=true&url=">
                                    <img width="30" src="{{theme_asset(path: 'public/assets/front-end/img/blogs/linkedin.svg')}}" alt="">
                                </a>

                                <a href="javascript:"
                                   class="share-on-social-media"
                                   data-action="{{ route('frontend.blog.details', ['slug' => $blogData['slug'] ?? null]) }}"
                                   data-social-media-name="api.whatsapp.com/send?text=">
                                    <img width="30" src="{{theme_asset(path: 'public/assets/front-end/img/blogs/whatsapp.svg')}}" alt="">
                                </a>

                            </div>
                        </div>

                        <div class="">
                            <div class="d-flex justify-content-between align-items-center gap-3 mb-4">
                                <h2 class="fw-bold mb-0 fs-22 fs-16-mobile">
                                    {{ translate('Popular_articles') }}
                                </h2>

                                <a href="{{ isset($blogPlatform) && $blogPlatform == 'app' ? route('app.blog.popular-blog', ['locale' => request('locale'), 'theme' => request('theme')]) : route('frontend.blog.popular-blog') }}" class="fs-12-mobile">
                                    {{ translate('See_more') }}
                                    <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 17 17" fill="none">
                                        <path d="M10.8367 2.6847C10.6187 2.4591 10.256 2.4591 10.0304 2.6847C9.81239 2.90267 9.81239 3.26546 10.0304 3.48292L14.119 7.57158H0.626997C0.312484 7.57209 0.0625 7.82208 0.0625 8.13659C0.0625 8.4511 0.312484 8.70922 0.626997 8.70922H14.119L10.0304 12.7903C9.81239 13.0159 9.81239 13.3791 10.0304 13.5966C10.256 13.8222 10.6192 13.8222 10.8367 13.5966L15.8933 8.54002C16.1189 8.32204 16.1189 7.95926 15.8933 7.7418L10.8367 2.6847Z" fill="currentColor"/>
                                    </svg>
                                </a>
                            </div>

                            <div class="row g-4 mb-3">
                                @foreach($popularBlogList as $blogItem)
                                    <div class="col-lg-4 col-md-6">
                                        @include('web-views.blogs.partials._single-blog-card', ['blogItem' => $blogItem])
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/blog.js') }}"></script>
@endpush
