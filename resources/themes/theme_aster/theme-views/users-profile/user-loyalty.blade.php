@extends('theme-views.layouts.app')

@section('title', translate('my_Loyalty_Point').' | '.$web_config['company_name'].' '.translate('ecommerce'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ theme_asset('assets/css/daterangepicker.css') }}">
@endpush

@section('content')
    <main class="main-content d-flex flex-column gap-3 py-3 mb-5">
        <div class="container">
            <div class="row g-3">
                @include('theme-views.partials._profile-aside')
                <div class="col-lg-9">
                    <div class="card mb-md-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between gap-2">
                                <h5 class="mb-4 text-capitalize">{{translate('loyalty_point')}}</h5>
                                <span class="text-dark d-md-none" data-bs-toggle="modal"
                                      data-bs-target="#instructionModal"><i class="bi bi-info-circle"></i></span>
                            </div>
                            <div class="d-flex flex-column flex-md-row gap-4 justify-content-center">
                                <div class="wallet-card pb-3 rounded-10 ov-hidden mn-w loyalty-point-card"
                                     data-bg-img="{{ theme_asset('assets/img/media/loyalty-card.png') }}">
                                    <div class="card-body d-flex flex-column gap-2 absolute-white">
                                        <img width="34" src="{{theme_asset('assets/img/icons/loyalty-point.png')}}"
                                             alt="" class="dark-support">
                                        <h2 class="fs-36 absolute-white"> {{ $totalLoyaltyPoint }}</h2>
                                        <p>{{translate('total_points')}}</p>
                                    </div>
                                </div>
                                <div class="">
                                    <div class="d-none d-md-block">
                                        <h6 class="mb-3">{{translate('how_to_use')}}</h6>
                                        <ul>
                                            <li>{{translate('convert_your_loyalty_point_to_wallet_money.')}}</li>
                                            <li>{{ translate('minimum').' '.$loyaltyPointMinimumPoint.' '.translate('points_required_to_convert_into_currency')}}</li>
                                        </ul>
                                    </div>
                                    <div class="d-flex justify-content-center justify-content-md-start">
                                        @if ($walletStatus == 1 && $loyaltyPointStatus == 1)
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                                    data-bs-target="#convertToCurrency">
                                                {{ translate('convert_to_currency') }}
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div
                                class="d-flex flex-column flex-md-row gap-2 justify-content-between mb-4 align-items-md-center">
                                <h5 class="text-capitalize">{{ translate('transaction_history') }}</h5>

                                <div class="dropdown">
                                    <button type="button"
                                        id="transactionFilterBtn"
                                        class="btn border-dark border-opacity-25 px-3 py-1 text-dark fs-14 d-flex align-items-center gap-10"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        {{ translate('filter') }}
                                        <span class="position-relative">
                                            <i class="bi bi-funnel-fill text--primary {{ $filterCount > 0 ? 'fs-20' : '' }}"></i>
                                            @if($filterCount > 0)
                                                <span class="count bg-danger top-0">
                                                {{ $filterCount }}
                                                </span>
                                            @endif
                                        </span>
                                    </button>

                                    <div class="dropdown-menu dropdown-menu-end shadow transaction-filter_dropdown">
                                        <form action="{{ route('loyalty') }}" method="get">
                                            <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                                                <h5> {{ translate('filter_data') }}</h5>
                                                <button id="filterCloseBtn" type="button" class="btn bg-badge text-absolute-white border-0 rounded-circle fs-10 lh-1 p-1 m-0">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </div>
                                            <div class="p-3 overflow-auto max-h-290px">
                                                <div class="mb-4">
                                                    <h6 class="mb-3"> {{ translate('filter_by') }}</h6>
                                                    <div class="d-flex gap-3 transaction_filter_by">
                                                        <label type="button"
                                                               class="btn p-2 min-w-60px {{ $filterBy == '' || $filterBy == 'all' ? 'btn-outline-primary' : 'btn-outline-secondary' }}">
                                                            {{ translate('all') }}
                                                            <input type="radio" name="filter_by" hidden
                                                                   value="all" {{ $filterBy == '' || $filterBy == 'all' ? 'checked' : '' }}>
                                                        </label>
                                                        <label type="button"
                                                               class="btn p-2 min-w-60px {{ $filterBy == 'debit' ? 'btn-outline-primary' : 'btn-outline-secondary' }}">
                                                            {{ translate('debit') }}
                                                            <input type="radio" name="filter_by" hidden
                                                                   value="debit" {{ $filterBy == 'debit' ? 'checked' : '' }}>
                                                        </label>
                                                        <label type="button"
                                                               class="btn p-2 min-w-60px {{ $filterBy == 'credit' ? 'btn-outline-primary' : 'btn-outline-secondary' }}">
                                                            {{ translate('credit') }}
                                                            <input type="radio" name="filter_by" hidden
                                                                   value="credit" {{ $filterBy == 'credit' ? 'checked' : '' }}>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="mb-4">
                                                    <h6 class="mb-3"> {{ translate('date_range') }}</h6>
                                                    <div class="position-relative">
                                                        <span class="bi bi-calendar icon-absolute-on-right"></span>
                                                        <input type="text" id="dateRangeInput" name="transaction_range" placeholder="{{ translate('Select_Date') }}"
                                                               class="form-control" value="{{ $transactionRange ?? '' }}"
                                                        />
                                                    </div>
                                                </div>
                                                <div class="mb-4">
                                                    <h6 class="mb-3"> {{ translate('earn_by') }}</h6>
                                                    <div class="d-flex flex-column gap-3 transaction_earn_by">
                                                        <label
                                                            class="d-flex justify-content-between align-items-center">
                                                            <span>{{ translate('Order_Transactions') }}</span>
                                                            <input type="checkbox" class="earn-checkbox" name="types[]"
                                                                   value="order_place"
                                                                {{ in_array('order_place', $transactionTypes) ? 'checked' : '' }}>
                                                        </label>
                                                        <label
                                                            class="d-flex justify-content-between align-items-center">
                                                            <span>{{ translate('Refund_Order') }}</span>
                                                            <input type="checkbox" class="earn-checkbox border-dark"
                                                                   name="types[]" value="refund_order"
                                                                {{ in_array('refund_order', $transactionTypes) ? 'checked' : '' }}>
                                                        </label>
                                                        <label
                                                            class="d-flex justify-content-between align-items-center">
                                                            <span>{{ translate('Point_to_wallet') }}</span>
                                                            <input type="checkbox" class="earn-checkbox border-dark"
                                                                   name="types[]" value="point_to_wallet"
                                                                {{ in_array('point_to_wallet', $transactionTypes) ? 'checked' : '' }}>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card shadow-lg p-3 d-flex flex-row gap-3">
                                                <a href="{{ route('loyalty') }}" class="btn btn-outline-primary w-100">
                                                    {{ translate('clear_filter') }}
                                                </a>
                                                <button type="submit" class="btn btn-primary w-100">
                                                    {{ translate('filter') }}
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex flex-column gap-2">
                                @foreach($loyaltyPointList as $key => $item)
                                    <div class="bg-light p-3 p-sm-4 rounded d-flex justify-content-between gap-3">
                                        <div class="align-items-start d-flex flex-column">
                                            <h4 class="mb-2 direction-ltr">
                                                {{ $item['debit'] != 0 ? ' - '.$item['debit'] : ' + '.$item['credit'] }}
                                            </h4>
                                            <h6 class="text-muted">{{ucwords(translate($item['transaction_type']))}}</h6>
                                        </div>
                                        <div class="text-end">
                                            <div class="text-muted mb-1">{{date('d M, Y H:i A',strtotime($item['created_at']))}} </div>
                                            @if($item['debit'] != 0)
                                                <p class="text-danger fs-12">{{translate('debit')}}</p>
                                            @else
                                                <p class="text-info fs-12">{{translate('credit')}}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if($loyaltyPointList->count()==0)
                                <div class="d-flex flex-column gap-3 align-items-center text-center my-5">
                                    <img width="72"
                                         src="{{theme_asset('assets/img/media/empty-transaction-history.png')}}"
                                         class="dark-support" alt="">
                                    <h6 class="text-muted">{{translate('you_don’t_have_any')}}
                                        <br> {{translate('transaction_yet')}}
                                    </h6>
                                </div>
                            @endif
                            <div class="card-footer bg-transparent border-0 p-0 mt-3">
                                {{ $loyaltyPointList->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <div class="modal fade" id="convertToCurrency" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header px-sm-5">
                    <h1 class="modal-title fs-5" id="reviewModalLabel">{{translate('convert_to_currency')}}</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{route('loyalty-exchange-currency')}}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="text-start mb-2">
                            {{translate('your_loyalty_point_will_convert_to_currency_and_transfer_to_your_wallet')}}
                        </div>
                        <div class="text-center">
                            <span class="text-warning">
                                {{translate('minimum_point_for_convert_to_currency_is').':'}} {{ $loyaltyPointMinimumPoint }} {{translate('point')}}
                            </span>
                        </div>
                        <div class="text-center mb-2">
                            <span>
                                {{ $loyaltyPointExchangeRate }} {{translate('point')}} = {{webCurrencyConverter(1)}}
                            </span>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-12">
                                <input class="form-control" type="number" name="point" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-bs-dismiss="modal" aria-label="Close"
                                class="btn btn-secondary">{{translate('close')}}</button>
                        <button type="submit" class="btn btn-primary">{{translate('submit')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="instructionModal" tabindex="-1" aria-labelledby="instructionModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="instructionModalLabel">{{ translate('how_to_use') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul>
                        <li>{{translate('convert_your_loyalty_point_to_wallet_money.')}}</li>
                        <li>{{translate('minimum')}} {{ $loyaltyPointMinimumPoint }} {{translate('points_required_to_convert')}}
                            <br>{{translate('into_currency')}}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ theme_asset('assets/js/moment.min.js') }}"></script>
    <script src="{{ theme_asset('assets/js/daterangepicker.min.js') }}"></script>
    <script src="{{ theme_asset('assets/js/user-wallet.js') }}"></script>
@endpush
