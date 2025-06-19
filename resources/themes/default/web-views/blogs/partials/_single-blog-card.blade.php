<div class="blog-card card rounded-10 border-0 h-100 blog-single-card-item"
     @if(isset($blogPlatform) && $blogPlatform == 'app')
         data-route="{{ route('app.blog.details', ['slug' => $blogItem?->slug, 'locale' => request('locale'), 'theme' => request('theme')]) }}"
     @else
         data-route="{{ route('frontend.blog.details', ['slug' => $blogItem?->slug]) }}"
    @endif
>
    <div class="blog-card_image">
        <img class="max-height-200px w-100 aspect-2"
             src="{{ getStorageImages(path: $blogItem?->thumbnail_full_url, type:'wide-banner') }}"
             alt="{{ $blogItem?->title }}">
    </div>
    <div class="p-3 d-flex flex-column gap-12px">
        @if(isset($blogPlatform) && $blogPlatform == 'app')
            <h3 class="mb-0 fw-semibold line-clamp-2">
                <a href="{{ route('app.blog.details', ['slug' => $blogItem?->slug, 'locale' => request('locale'), 'theme' => request('theme')]) }}" class="line-clamp-2 fs-18 fs-16-mobile">
                    {{ $blogItem?->title }}
                </a>
            </h3>

            @if($blogItem?->category?->name)
                <a href="{{ route('app.blog.index', ['category' => $blogItem?->category?->name, 'locale' => request('locale'), 'theme' => request('theme')]) }}" title="{{ $blogItem?->category?->name }}" class="border rounded-30 px-3 py-1 mb-0 fs-12-mobile line-clamp-1 w-fit-content">
                    <span class="opacity-60">{{ Str::limit($blogItem?->category?->name, 25) ?? translate('Uncategorized') }}</span>
                </a>
            @endif
        @else
            <h3 class="mb-0 fw-semibold line-clamp-2">
                <a href="{{ route('frontend.blog.details', ['slug' => $blogItem?->slug]) }}" class="line-clamp-2 fs-18 fs-16-mobile">
                    {{ $blogItem?->title }}
                </a>
            </h3>

            @if($blogItem?->category?->name)
                <a href="{{ route('frontend.blog.index', ['category' => $blogItem?->category?->name]) }}" title="{{ $blogItem?->category?->name }}" class="border rounded-30 px-3 py-1 mb-0 fs-12-mobile line-clamp-1 w-fit-content">
                    <span class="opacity-60">{{ Str::limit($blogItem?->category?->name, 25) ?? translate('Uncategorized') }}</span>
                </a>
            @endif
        @endif

        <div class="pb-3">
            <div class="blog-card_footer">
                <div class="d-flex justify-content-between align-content-center gap-3">
                    @if($blogItem?->writer)
                        @if(isset($blogPlatform) && $blogPlatform == 'app')
                            <span class="opacity-80 fs-14 d-flex gap-1 fs-12-mobile text-nowrap">
                                {{ translate('By') }}
                                <a href="{{ route('app.blog.index', ['writer' => $blogItem?->writer, 'locale' => request('locale'), 'theme' => request('theme')]) }}" class="fw-semibold max-width-20ch line-clamp-1"
                                   title="{{ $blogItem?->writer }}">
                                    {{ Str::limit($blogItem?->writer, 40, '...') }}
                                </a>
                            </span>
                        @else
                            <span class="opacity-80 fs-14 d-flex gap-1 fs-12-mobile text-nowrap">
                                {{ translate('By') }}
                                <a href="{{ route('frontend.blog.index', ['writer' => $blogItem?->writer]) }}" class="fw-semibold max-width-20ch line-clamp-1 fs-12-mobile"
                                   title="{{ $blogItem?->writer }}">
                                    {{ Str::limit($blogItem?->writer, 40, '...') }}
                                </a>
                            </span>
                        @endif
                    @endif
                    <span class="opacity-70 text-nowrap fs-14 fs-12-mobile flex-grow-1 text-end">{{ $blogItem->publish_date->diffForHumans() }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
