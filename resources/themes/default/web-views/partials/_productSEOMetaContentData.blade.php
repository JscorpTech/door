@if(isset($productDetails) && isset($metaContentData))
    @if($metaContentData?->title)
        <meta name="title" content="{{ $metaContentData?->title }}">
        <meta property="og:title" content="{{ $metaContentData?->title }}">
        <meta name="twitter:title" content="{{ $metaContentData?->title }}">
    @else
        <meta name="title" content="{{ $productDetails?->name }}">
        <meta property="og:title" content="{{ $productDetails?->name }}">
        <meta name="twitter:title" content="{{ $productDetails?->name }}">
    @endif

    @if($metaContentData?->description)
        <meta name="description" content="{!! Str::limit($metaContentData?->description, 160) !!}">
        <meta property="og:description" content="{!! Str::limit($metaContentData?->description, 160) !!}">
        <meta name="twitter:description" content="{!! Str::limit($metaContentData?->description, 160) !!}">
    @else
        <meta name="description" content="@foreach(explode(' ',$productDetails['name']) as $keyword) {{$keyword.' , '}} @endforeach">
        <meta property="og:description" content="@foreach(explode(' ',$productDetails['name']) as $keyword) {{$keyword.' , '}} @endforeach">
        <meta name="twitter:description" content="@foreach(explode(' ',$productDetails['name']) as $keyword) {{$keyword.' , '}} @endforeach">
    @endif

    <meta name="keywords" content="@foreach(explode(' ',$productDetails['name']) as $keyword) {{$keyword.' , '}} @endforeach">

    @if($productDetails->added_by == 'seller')
        <meta name="author" content="{{ $productDetails->seller->shop?$productDetails->seller->shop->name:$productDetails->seller->f_name}}">
    @elseif($productDetails->added_by == 'admin')
        <meta name="author" content="{{$web_config['company_name']}}">
    @endif

    <meta property="og:image" content="{{ $metaContentData?->image_full_url['path'] }}">
    <meta name="twitter:image" content="{{ $metaContentData?->image_full_url['path'] }}">

    <meta property="og:url" content="{{ route('product', [$productDetails->slug]) }}">
    <meta name="twitter:url" content="{{ route('product', [$productDetails->slug]) }}">

    @if($metaContentData?->index != 'noindex')
        <meta name="robots" content="index">
    @endif

    @if($metaContentData?->no_follow || $metaContentData?->no_image_index || $metaContentData?->no_archive || $metaContentData?->no_snippet)
        <meta name="robots" content="{{ ($metaContentData?->no_follow ? 'nofollow' : '') . ($metaContentData?->no_image_index ? ' noimageindex' : '') . ($metaContentData?->no_archive ? ' noarchive' : '') . ($metaContentData?->no_snippet ? ' nosnippet' : '') }}">
    @endif

    @if($metaContentData?->meta_max_snippet)
        <meta name="robots" content="max-snippet{{ $metaContentData?->max_snippet_value ? ': ' . $metaContentData?->max_snippet_value : '' }}">
    @endif

    @if($metaContentData?->max_video_preview)
        <meta name="robots" content="max-video-preview{{ $metaContentData?->max_video_preview_value ? ': ' . $metaContentData?->max_video_preview_value : '' }}">
    @endif

    @if($metaContentData?->max_image_preview)
        <meta name="robots" content="max-image-preview{{ $metaContentData?->max_image_preview_value ? ': ' . $metaContentData?->max_image_preview_value : '' }}">
    @endif
@endif
