@include('theme-views.product.partials._filter-product-type')
@include('theme-views.product.partials._filter-product-price')
@include('theme-views.product.partials._filter-product-categories', [
    'productCategories' => $categories,
    'dataFrom' => 'shop',
])
@include('theme-views.product.partials._filter-product-brands', [
    'productBrands' => $brands,
    'dataFrom' => 'shop',
])

@include('theme-views.product.partials._filter-publishing-houses', [
    'productPublishingHouses' => $shopPublishingHouses,
    'dataFrom' => 'shop',
])

@include('theme-views.product.partials._filter-product-authors', [
    'productAuthors' => $digitalProductAuthors,
    'dataFrom' => 'shop',
])

@include('theme-views.product.partials._filter-product-reviews', [
    'productRatings' => $ratings
])
