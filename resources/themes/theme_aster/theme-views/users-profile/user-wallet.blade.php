@extends('theme-views.layouts.app')

@section('title', translate('My_Wallet').' | '.$web_config['company_name'].' '.translate('ecommerce'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ theme_asset('assets/css/daterangepicker.css') }}">
@endpush

@section('content')
    <main class="main-content d-flex flex-column gap-3 py-3 mb-4">
        <div class="container">
            <div class="row g-3">
                @include('theme-views.partials._profile-aside')
                <div class="col-lg-9">
                    <div class="row g-0 g-md-3 h-100">
                        @php($addFundsToWallet = getWebConfig(name: 'add_funds_to_wallet'))
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between gap-2">
                                        <h5 class="mb-4 flex-grow-1 text-capitalize">{{ translate('my_wallet') }}</h5>
                                        <span class="text-dark d-md-none" data-bs-toggle="modal"
                                              data-bs-target="#instructionModal"><i
                                                class="bi bi-info-circle"></i></span>
                                    </div>
                                    <div class="wallet-card pb-3 rounded-10 overlay ov-hidden"
                                         data-bg-img="{{ theme_asset('assets/img/media/wallet-card.png') }}"
                                         style="--bg-color: var(--bs-primary-rgb);">
                                        <div class="card-body d-flex flex-column gap-2 absolute-white">
                                            <div class="d-flex justify-content-between mb-3">
                                                <img width="34"
                                                     src="{{theme_asset('assets/img/icons/profile-icon5.png') }}" alt=""
                                                     class="dark-support">
                                                @if ($addFundsToWallet)
                                                    <button class="btn btn-light text--base align-items-center"
                                                            data-bs-toggle="modal" data-bs-target="#addFundToWallet">
                                                        <i class="bi bi-plus-circle-fill text--primary fs-18"></i>
                                                        <strong
                                                            class="text--primary text-capitalize">{{ translate('add_fund') }}</strong>
                                                    </button>
                                                @endif
                                            </div>
                                            <h2 class="fs-36 absolute-white d-flex align-items-center">
                                                {{ webCurrencyConverter($totalWalletBalance ?? 0) }}
                                                @if ($addFundsToWallet)
                                                    <span class="ms-2 fs-18 d-none d-sm-block" data-bs-toggle="tooltip"
                                                          data-bs-placement="top"
                                                          title="{{ translate('this_wallet_balance_can_be_used_for_product_purchase_and_if_want_to_add_more_fund_to_wallet_click_on_add_fund') }}"
                                                    >
                                                    <i class="bi bi-info-circle"></i>
                                                </span>
                                                @endif
                                            </h2>

                                            <p class="text-capitalize">{{ translate('total_balance') }}</p>
                                        </div>
                                    </div>

                                    @if($addFundsToWallet)
                                        <div class="mt-4 d-none d-md-block">
                                            <div class="swiper add-fund-swiper" data-swiper-loop="true"
                                                 data-swiper-margin="16">
                                                <div class="swiper-wrapper">
                                                    @foreach ($addFundBonusList as $bonus)
                                                        <div class="swiper-slide d-block">
                                                            <div
                                                                class="add-fund-swiper-card position-relative z-1 w-100 border border-primary rounded-10 p-4">
                                                                <div class="w-100 mb-2">
                                                                    <h4 class="mb-2 text-primary">{{ $bonus->title }}</h4>
                                                                    <p class="mb-2 text-dark">{{ translate('valid_till') }} {{ date('d M, Y',strtotime($bonus->end_date_time)) }}</p>
                                                                </div>
                                                                <div>
                                                                    @if ($bonus->bonus_type == 'percentage')
                                                                        <p>{{ translate('add_fund_to_wallet') }} {{ webCurrencyConverter($bonus->min_add_money_amount) }} {{ translate('and_enjoy') }} {{ $bonus->bonus_amount }}
                                                                            % {{ translate('bonus') }}</p>
                                                                    @else
                                                                        <p>{{ translate('add_fund_to_wallet') }} {{ webCurrencyConverter($bonus->min_add_money_amount) }} {{ translate('and_enjoy') }} {{ webCurrencyConverter($bonus->bonus_amount) }} {{ translate('bonus') }}</p>
                                                                    @endif
                                                                    <p class="fw-bold text-primary mb-0">{{ $bonus->description ? Str::limit($bonus->description, 50):'' }}</p>
                                                                </div>
                                                                <img class="slider-card-bg-img" width="50"
                                                                     src="{{ theme_asset('assets/img/media/add_fund_vector.png') }}"
                                                                     alt="">
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                <div class="swiper-pagination position-relative mt-3"></div>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="mt-4 d-none d-md-block">
                                        <h6 class="mb-3">{{ translate('how_to_use') }}</h6>
                                        <ul>
                                            <li>{{ translate('Earn_money_to_your_wallet_by_completing_the_offer_&_challenged') }}</li>
                                            <li>{{ translate('Convert_your_loyalty_points_into_wallet_money') }}</li>
                                            <li>{{ translate('Admin_also_reward_their_top_customers_with_wallet_money') }}</li>
                                            <li>{{ translate('Send_your_wallet_money_while_order') }}</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8 mt-md-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex flex-column flex-md-row gap-2 justify-content-between mb-4 align-items-md-center">
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
                                                <form action="{{ route('wallet') }}" method="get">
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
                                                                <input type="text" id="dateRangeInput" name="transaction_range"
                                                                       class="form-control" placeholder="{{ translate('Select_Date') }}" value="{{ $transactionRange ?? '' }}"
                                                                />
                                                            </div>
                                                        </div>
                                                        <div class="mb-4">
                                                            <h6 class="mb-3"> {{ translate('earn_by') }}</h6>
                                                            <div class="d-flex flex-column gap-3 transaction_earn_by">
                                                                <label
                                                                    class="d-flex justify-content-between align-items-center">
                                                                    <span>{{ translate('Order_Transactions') }}</span>
                                                                    <input type="checkbox" class="earn-checkbox"
                                                                           name="types[]" value="order_place"
                                                                        {{ in_array('order_place', $transactionTypes) ? 'checked' : '' }}>
                                                                </label>
                                                                <label
                                                                    class="d-flex justify-content-between align-items-center">
                                                                    <span>{{ translate('Order_Refund') }}</span>
                                                                    <input type="checkbox"
                                                                           class="earn-checkbox border-dark"
                                                                           name="types[]" value="order_refund"
                                                                        {{ in_array('order_refund', $transactionTypes) ? 'checked' : '' }}>
                                                                </label>
                                                                <label
                                                                    class="d-flex justify-content-between align-items-center">
                                                                    <span>{{ translate('Converted_from_Loyalty_Point') }}</span>
                                                                    <input type="checkbox"
                                                                           class="earn-checkbox border-dark"
                                                                           name="types[]" value="loyalty_point"
                                                                        {{ in_array('loyalty_point', $transactionTypes) ? 'checked' : '' }}>
                                                                </label>
                                                                <label
                                                                    class="d-flex justify-content-between align-items-center">
                                                                    <span>{{ translate('Added_via_Payment_Method') }}</span>
                                                                    <input type="checkbox"
                                                                           class="earn-checkbox border-dark"
                                                                           name="types[]" value="add_fund"
                                                                        {{ in_array('add_fund', $transactionTypes) ? 'checked' : '' }}>
                                                                </label>
                                                                <label
                                                                    class="d-flex justify-content-between align-items-center">
                                                                    <span>{{ translate('add_fund_by_admin') }}</span>
                                                                    <input type="checkbox"
                                                                           class="earn-checkbox border-dark"
                                                                           name="types[]" value="add_fund_by_admin"
                                                                        {{ in_array('add_fund_by_admin', $transactionTypes) ? 'checked' : '' }}>
                                                                </label>
                                                                <label
                                                                    class="d-flex justify-content-between align-items-center">
                                                                    <span>{{ translate('Earned_by_referral') }}</span>
                                                                    <input type="checkbox"
                                                                           class="earn-checkbox border-dark"
                                                                           name="types[]" value="earned_by_referral"
                                                                        {{ in_array('earned_by_referral', $transactionTypes) ? 'checked' : '' }}>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card shadow-lg p-3 d-flex flex-row gap-3">
                                                        <a href="{{ route('wallet') }}" class="btn btn-outline-primary w-100">
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
                                        @foreach($walletTransactionList as $key=>$item)
                                            @if ($item['admin_bonus'] > 0)
                                                <div
                                                    class="bg-light p-3 p-sm-4 rounded d-flex justify-content-between gap-3">
                                                    <div class="">
                                                        <h4 class="mb-2 d-flex align-items-center gap-2">
                                                            <img src="{{ theme_asset('assets/img/icons/coin-success.png') }}"
                                                                width="25" alt="">
                                                             {{'+'. webCurrencyConverter($item['admin_bonus']) }}
                                                        </h4>
                                                        <h6 class="text-muted">
                                                            {{ translate('admin_bonus') }}
                                                        </h6>
                                                    </div>
                                                    <div class="text-end">
                                                        <div class="text-muted mb-1">{{date('d M, Y H:i A',strtotime($item['created_at']))}} </div>
                                                        @if($item['debit'] != 0)
                                                            <p class="text-danger fs-12">{{ translate('debit') }}</p>
                                                        @else
                                                            <p class="text-info fs-12">{{ translate('credit') }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif

                                            <div
                                                class="bg-light p-3 p-sm-4 rounded d-flex justify-content-between gap-3">
                                                <div class="">
                                                    <h4 class="mb-2 d-flex align-items-center gap-2">
                                                        @if($item['debit'] != 0)
                                                            <img src="{{ theme_asset('assets/img/icons/coin-danger.png') }}"
                                                                width="25" alt="">
                                                        @else
                                                            <img src="{{ theme_asset('assets/img/icons/coin-success.png') }}"
                                                                width="25" alt="">
                                                        @endif

                                                        <span class="absolute-ltr font-bold fs-18">
                                                            {{ $item['debit'] != 0 ? ' - '.webCurrencyConverter(amount: $item['debit']): ' + '.webCurrencyConverter(amount: $item['credit']) }}
                                                        </span>
                                                    </h4>
                                                    <h6 class="text-muted">
                                                        @if ($item['transaction_type'] == 'add_fund_by_admin')
                                                            {{ translate('add_fund_by_admin') }} {{ $item['reference'] =='earned_by_referral' ? '('.translate($item['reference']).')' : '' }}
                                                        @elseif($item['transaction_type'] == 'order_place')
                                                            {{ translate('order_place') }}
                                                        @elseif($item['transaction_type'] == 'loyalty_point')
                                                            {{ translate('converted_from_loyalty_point') }}
                                                        @elseif($item['transaction_type'] == 'add_fund')
                                                            {{ translate('added_via_payment_method') }}
                                                        @else
                                                            {{ ucwords(translate($item['transaction_type'])) }}
                                                        @endif
                                                    </h6>
                                                </div>
                                                <div class="text-end">
                                                    <div class="text-muted mb-1">
                                                        {{ date('d M, Y H:i A',strtotime($item['created_at'])) }}
                                                    </div>
                                                    @if($item['debit'] != 0)
                                                        <p class="text-danger fs-12">{{ translate('Debit') }}</p>
                                                    @else
                                                        <p class="text-info fs-12">{{ translate('Credit') }}</p>
                                                    @endif
                                                </div>
                                            </div>

                                        @endforeach
                                    </div>
                                    @if($walletTransactionList->count()==0)
                                        <div class="d-flex flex-column gap-3 align-items-center text-center my-5">
                                            <img width="72"
                                                 src="{{theme_asset('assets/img/media/empty-transaction-history.png') }}"
                                                 class="dark-support" alt="">
                                            <h6 class="text-muted">{{ translate('You_do_not_have_any') }}
                                                <br> {{ request('type') != 'all' ? ucwords(translate(request('type'))) : '' }} {{ translate('transaction_yet') }}
                                            </h6>
                                        </div>
                                    @endif

                                    <div class="card-footer bg-transparent border-0 p-0 mt-3">
                                        @if (request()->has('type'))
                                            @php($paginationLinks = $walletTransactionList->links())
                                            @php($modifiedLinks = preg_replace('/href="([^"]*)"/', 'href="$1&type='.request('type').'"', $paginationLinks))
                                        @else
                                            @php($modifiedLinks = $walletTransactionList->links())
                                        @endif
                                        {!! $modifiedLinks !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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
                            <li>{{ translate('Earn_money_to_your_wallet_by_completing_the_offer_&_challenged') }}</li>
                            <li>{{ translate('Convert_your_loyalty_points_into_wallet_money') }}</li>
                            <li>{{ translate('Admin_also_reward_their_top_customers_with_wallet_money') }}</li>
                            <li>{{ translate('Send_your_wallet_money_while_order') }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        @if ($addFundsToWallet)
            <div class="modal fade" id="addFundToWallet" tabindex="-1" aria-labelledby="addFundToWalletModalLabel"
                 aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-md">
                    <div class="modal-content">
                        <div class="text-end p-3">
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body px-5">

                            <form action="{{ route('customer.add-fund-request') }}" method="post"
                                  id="add_fund_to_wallet_Form">
                                @csrf
                                <div class="pb-4">
                                    <h4 class="text-center pb-3">{{ translate('add_Fund_to_Wallet') }}</h4>
                                    <p class="text-center pb-3">{{ translate('add_fund_by_from_secured_digital_payment_gateways') }}</p>
                                    <input type="number" class="h-70 form-control text-center text-24 rounded-10"
                                           id="add-fund-amount-input" min="1" name="amount" autocomplete="off" required
                                           placeholder="{{ \App\Utils\currency_symbol() }}500">
                                    <input type="hidden" value="web" name="payment_platform" required>
                                    <input type="hidden" value="{{ request()->url() }}" name="external_redirect_link"
                                           required>
                                </div>

                                <div id="add-fund-list-area d-none">
                                    <h5 class="mb-4 text-capitalize">{{ translate('payment_methods') }}
                                        <small>({{ translate('faster_&_secure_way_to_pay_bill') }})</small></h5>
                                    <div class="gatways_list">
                                        @forelse ($paymentGatewayList as $gateway)
                                            <label class="form-check form--check rounded">
                                                <input type="radio" class="form-check-input d-none"
                                                       name="payment_method" value="{{ $gateway->key_name }}" required>
                                                <div class="check-icon">
                                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                                         xmlns="http://www.w3.org/2000/svg">
                                                        <circle cx="8" cy="8" r="8" fill="var(--bs-primary)"/>
                                                        <path
                                                            d="M9.18475 6.49574C10.0715 5.45157 11.4612 4.98049 12.8001 5.27019L7.05943 11.1996L3.7334 7.91114C4.68634 7.27184 5.98266 7.59088 6.53004 8.59942L6.86856 9.22314L9.18475 6.49574Z"
                                                            fill="white"/>
                                                    </svg>
                                                </div>
                                                @php( $payment_method_title = !empty($gateway->additional_data) ? (json_decode($gateway->additional_data)->gateway_title ?? ucwords(str_replace('_',' ', $gateway->key_name))) : ucwords(str_replace('_',' ', $gateway->key_name)) )
                                                @php( $payment_method_img = !empty($gateway->additional_data) ? json_decode($gateway->additional_data)->gateway_image : '' )
                                                <div class="form-check-label gap-3 d-flex align-items-center">
                                                    <img width="60" alt=""
                                                         src="{{ getValidImage(path: 'storage/app/public/payment_modules/gateway_image/'.$payment_method_img, type:'banner') }}">
                                                    <span>{{ $payment_method_title }}</span>
                                                </div>
                                            </label>
                                        @empty

                                        @endforelse
                                    </div>
                                </div>
                                <div class="d-flex justify-content-center pt-2 pb-3">
                                    <button type="submit" class="btn btn-primary w-75 mx-3 text-capitalize"
                                            id="add-fund-to-wallet-form-btn">
                                        {{ translate('add_fund') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </main>
@endsection


@push('script')
    <script src="{{ theme_asset('assets/js/moment.min.js') }}"></script>
    <script src="{{ theme_asset('assets/js/daterangepicker.min.js') }}"></script>
    <script src="{{ theme_asset('assets/js/user-wallet.js') }}"></script>
@endpush
