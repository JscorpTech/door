@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Session;
@endphp
@extends('layouts.back-end.app')

@section('title', translate('Clearance_Sale'))

@section('content')
    @php($direction = Session::get('direction'))
    <div class="content container-fluid">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img width="20" src="{{ dynamicAsset(path: 'public/assets/back-end/img/note.png') }}" alt="">
                {{ translate('Clearance_Sale') }}
            </h2>
        </div>

        @include('admin-views.deal.clearance-sale.partials.clearance-sale-inline-menu')
        @include('admin-views.deal.clearance-sale.partials._clearance-sale-offer-setup')
        @include('admin-views.deal.clearance-sale.partials._product-add-list')
        @include('admin-views.deal.clearance-sale.partials._product-add-modal')
        @include('admin-views.deal.clearance-sale.partials._discount-update-modal')
    </div>
@endsection

@push('script')
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/deal.js') }}"></script>
    <script src="{{dynamicAsset(path: 'public/assets/back-end/js/search-and-select-multiple-product.js')}}"></script>
@endpush
