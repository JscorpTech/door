@extends('layouts.front-end.app')

@section('title', translate('all_Categories'))

@push('css_or_js')
    <meta property="og:image" content="{{$web_config['web_logo']['path']}}"/>
    <meta property="og:title" content="Categories of {{$web_config['company_name']}} "/>
    <meta property="og:url" content="{{env('APP_URL')}}">
    <meta property="og:description" content="{{ $web_config['meta_description'] }}">
    <meta property="twitter:card" content="{{$web_config['web_logo']['path']}}"/>
    <meta property="twitter:title" content="Categories of {{$web_config['company_name']}}"/>
    <meta property="twitter:url" content="{{env('APP_URL')}}">
    <meta property="twitter:description" content="{{ $web_config['meta_description'] }}">
@endpush

@section('content')
    <div class="container rtl __inline-52 text-align-direction">

        <div class="bg-primary-light rounded-10 my-4 p-3 p-sm-4" data-bg-img="{{ theme_asset(path: 'public/assets/front-end/img/media/bg.png') }}">
             <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex flex-column gap-1 text-primary">
                    <h4 class="mb-0 text-start fw-bold text-primary text-uppercase">
                        {{ translate('category') }}
                    </h4>
                    <p class="fs-14 fw-semibold mb-0">
                        {{translate('Find_your_favorite_categories_and_products')}}
                    </p>
                </div>
                 <form action="{{ route('categories') }}" method="GET">
                    <div class="input-group">
                        <input type="text" class="form-control rounded-10" placeholder="{{translate('Search_Categories')}}" name="search">
                        <div class="input-group-append">
                            <button class="btn btn--primary rounded-10" type="submit">
                                <i class="tio-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="brand_div-wrap mb-4">
            @foreach($categories as $categoryKey => $category)
            <a href="{{route('products', ['category_id'=> $category['id'],'data_from'=>'category','page'=>1])}}" class="brand_div">
                <img src="{{ getStorageImages(path: $category->icon_full_url, type: 'category') }}" alt="{{ $category['name'] }}">
                <div>{{ $category['name'] }}</div>
            </a>
            @endforeach
        </div>

    </div>
@endsection

@push('script')
    <script src="{{ asset('public/assets/front-end/js/categories.js') }}"></script>
@endpush
