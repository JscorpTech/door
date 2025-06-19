@if(!isset($productDetailsMeta) || !$productDetailsMeta)
    @if(isset($robotsMetaContentData) && $robotsMetaContentData?->meta_title)
        <title>{{ $robotsMetaContentData?->meta_title }}</title>
        <meta name="title" content="{{ $robotsMetaContentData?->meta_title }}">
        <meta property="og:title" content="{{ $robotsMetaContentData?->meta_title }}">
        <meta name="twitter:title" content="{{ $robotsMetaContentData?->meta_title }}">
    @elseif($web_config['default_meta_content'])
        <meta name="title" content="{{ $web_config['default_meta_content']['meta_title'] }} "/>
        <meta property="og:title" content="{{ $web_config['default_meta_content']['meta_title'] }} "/>
        <meta name="twitter:title" content="{{ $web_config['default_meta_content']['meta_title'] }}"/>
    @else
        <meta name="title" content="{{$web_config['company_name']}} "/>
        <meta property="og:title" content="{{$web_config['company_name']}} "/>
        <meta name="twitter:title" content="{{$web_config['company_name']}}"/>
    @endif

    @if(isset($robotsMetaContentData) && $robotsMetaContentData?->meta_description)
        <meta name="description" content="{{ $robotsMetaContentData?->meta_description }}">
        <meta property="og:description" content="{{ $robotsMetaContentData?->meta_description }}">
        <meta name="twitter:description" content="{{ $robotsMetaContentData?->meta_description }}">
    @elseif($web_config['default_meta_content'])
        <meta name="description" content="{{ substr(strip_tags(str_replace('&nbsp;', ' ', $web_config['default_meta_content']['meta_description'])),0,160) }}">
        <meta property="og:description" content="{{ substr(strip_tags(str_replace('&nbsp;', ' ', $web_config['default_meta_content']['meta_description'])),0,160) }}">
        <meta name="twitter:description" content="{{ substr(strip_tags(str_replace('&nbsp;', ' ', $web_config['default_meta_content']['meta_description'])),0,160) }}">
    @else
        <meta name="description" content="{{ $web_config['meta_description'] }}">
        <meta property="og:description" content="{{ $web_config['meta_description'] }}">
        <meta name="twitter:description" content="{{ $web_config['meta_description'] }}">
    @endif

    <meta property="og:url" content="{{ env('APP_URL') }}">
    <meta name="twitter:url" content="{{ env('APP_URL') }}">

    @if(isset($robotsMetaContentData) && $robotsMetaContentData?->meta_image_full_url['path'])
        <meta property="og:image" content="{{ $robotsMetaContentData?->meta_image_full_url['path'] }}">
        <meta name="twitter:image" content="{{ $robotsMetaContentData?->meta_image_full_url['path'] }}">
        <meta name="twitter:card" content="{{ $robotsMetaContentData?->meta_image_full_url['path'] }}">
    @elseif($web_config['default_meta_content'])
        <meta property="og:image" content="{{ $web_config['default_meta_content']?->meta_image_full_url['path'] }}"/>
        <meta name="twitter:image" content="{{ $web_config['default_meta_content']?->meta_image_full_url['path'] }}"/>
        <meta name="twitter:card" content="{{ $web_config['default_meta_content']?->meta_image_full_url['path'] }}"/>
    @else
        <meta property="og:image" content="{{$web_config['web_logo']['path']}}"/>
        <meta name="twitter:image" content="{{$web_config['web_logo']['path']}}"/>
        <meta name="twitter:card" content="{{$web_config['web_logo']['path']}}"/>
    @endif

    @if(isset($robotsMetaContentData) && $robotsMetaContentData?->canonicals_url)
        <link rel="canonical" href="{{ $robotsMetaContentData?->canonicals_url }}">
    @endif

    @if(isset($robotsMetaContentData) && $robotsMetaContentData?->index != 'noindex')
        <meta name="robots" content="index">
    @endif

    @if(isset($robotsMetaContentData) && ($robotsMetaContentData?->no_follow || $robotsMetaContentData?->no_image_index || $robotsMetaContentData?->no_archive || $robotsMetaContentData?->no_snippet))
        <meta name="robots" content="{{ ($robotsMetaContentData?->no_follow ? 'nofollow' : '') . ($robotsMetaContentData?->no_image_index ? ' noimageindex' : '') . ($robotsMetaContentData?->no_archive ? ' noarchive' : '') . ($robotsMetaContentData?->no_snippet ? ' nosnippet' : '') }}">
    @endif

    @if(isset($robotsMetaContentData) && $robotsMetaContentData?->meta_max_snippet)
        <meta name="robots" content="max-snippet{{ $robotsMetaContentData?->max_snippet_value ? ': ' . $robotsMetaContentData?->max_snippet_value : '' }}">
    @endif

    @if(isset($robotsMetaContentData) && $robotsMetaContentData?->max_video_preview)
        <meta name="robots" content="max-video-preview{{ $robotsMetaContentData?->max_video_preview_value ? ': ' . $robotsMetaContentData?->max_video_preview_value : '' }}">
    @endif

    @if(isset($robotsMetaContentData) && $robotsMetaContentData?->max_image_preview)
        <meta name="robots" content="max-image-preview{{ $robotsMetaContentData?->max_image_preview_value ? ': ' . $robotsMetaContentData?->max_image_preview_value : '' }}">
    @endif
@endif
