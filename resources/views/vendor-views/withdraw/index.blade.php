@extends('layouts.back-end.app-seller')

@section('title', translate('withdraw_Request'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/withdraw-icon.png')}}" alt="">
                {{translate('withdraw')}}
            </h2>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">

                    <div class="p-3">
                        <div class="row gy-1 align-items-center justify-content-between">
                            <div class="col-auto">
                                <h5 class="text-capitalize">
                                    {{ translate('withdraw_request_table')}}
                                    <span class="badge badge-soft-dark radius-50 fz-12 ml-1" id="withdraw-requests-count">{{ $withdrawRequests->total() }}</span>
                                </h5>
                            </div>
                            <div class="d-flex col-auto gap-3">
                                <div class="col-auto">
                                    <select name="status" class="custom-select max-w-200 status-filter" >
                                        <option value="all">{{translate('all')}}</option>
                                        <option value="approved">{{translate('approved')}}</option>
                                        <option value="denied">{{translate('denied')}}</option>
                                        <option value="pending">{{translate('pending')}}</option>
                                    </select>
                                </div>
                                <div class="dropdown">
                                    <a type="button" class="btn btn-outline--primary text-nowrap" href="{{route('vendor.business-settings.withdraw.export-withdraw-list',['searchValue'=> request('searchValue')??''])}}">
                                        <img width="14" src="{{dynamicAsset(path: 'public/assets/back-end/img/excel.png')}}" class="excel" alt="">
                                        <span class="ps-2">{{ translate('export') }}</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="status-wise-view">
                        @include('vendor-views.withdraw._table')
                    </div>
                </div>
            </div>
        </div>
    </div>
    <span id="get-status-filter-route" data-action="{{route('vendor.business-settings.withdraw.index')}}"></span>
@endsection
@push('script')
    <script src="{{dynamicAsset(path: 'public/assets/back-end/js/vendor/withdraw.js')}}"></script>
@endpush
