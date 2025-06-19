@php use Carbon\Carbon; @endphp
@extends('layouts.back-end.app')

@section('title', translate('subscriber_list'))

@section('content')
<div class="content container-fluid">
    <div class="mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img src="{{dynamicAsset(path: 'public/assets/back-end/img/subscribers.png')}}" width="20" alt="">
            {{translate('subscriber_list')}}
            <span class="badge badge-soft-dark radius-50 fz-14 ml-1">{{ $totalSubscribers }}</span>
        </h2>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ url()->current() }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">{{ translate('Subscription_Date') }}</label>
                        <div class="position-relative">
                            <span class="tio-calendar icon-absolute-on-right"></span>
                            <input type="text" name="subscription_date" value="{{ request('subscription_date', '') }}" class="js-daterangepicker-with-range form-control cursor-pointer" value="{{request('subscription_date')}}" placeholder="{{ translate('Select_Date') }}" autocomplete="off" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{translate('Sort_By')}}</label>
                        <select class="form-control js-select2-custom" name="sort_by">
                            <option disabled {{ is_null(request('sort_by')) ? 'selected' : '' }}>{{ translate('select_mail_sorting_order') }}</option>
                            <option value="asc" {{ request('sort_by') === 'asc' ? 'selected' : '' }}>{{ translate('Sort_by_oldest') }}</option>
                            <option value="desc" {{ request('sort_by') === 'desc' ? 'selected' : '' }}>{{ translate('Sort_by_newest') }}</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{translate('Choose_First')}}</label>
                        <input type="number" name="choose_first" min="1" value="{{ request('choose_first') }}" class="form-control" placeholder="{{translate('Ex')}} : {{translate('100')}}">
                    </div>
                </div>
                <div class="btn--container justify-content-end mt-3">
                    <a href="{{ route('admin.customer.subscriber-list') }}"
                       class="btn btn-secondary px-5">
                        {{ translate('reset') }}
                    </a>
                    <button type="submit" class="btn btn--primary">{{translate('Filter')}}</button>
                </div>
            </form>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header gap-3 align-items-center">
                    <h5 class="mb-0 mr-auto">
                        {{translate('subscriber_list')}}
                        <span class="badge badge-soft-dark radius-50 fz-14 ml-1">{{ $subscriberList->total() }}</span>
                    </h5>

                    <form action="{{ url()->current() }}" method="GET">
                        <input type="hidden" name="subscription_date" value="{{request('subscription_date')}}">
                        <input type="hidden" name="sort_by" value="{{ request('sort_by')}}">
                        <input type="hidden" name="choose_first" value="{{request('choose_first')}}">
                        <div class="input-group input-group-merge input-group-custom">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="tio-search"></i>
                                </div>
                            </div>
                            <input id="datatableSearch_" type="search" name="searchValue" class="form-control"
                                placeholder="{{ translate('search_by_email')}}"  aria-label="Search orders" value="{{ request('searchValue') }}">
                            <button type="submit" class="btn btn--primary">{{ translate('search')}}</button>
                        </div>
                    </form>
                    <div class="dropdown">
                        <a type="button" class="btn btn-outline--primary text-nowrap" href="{{route('admin.customer.subscriber-list.export', ['sort_by' => request('sort_by'), 'choose_first' => request('choose_first'), 'subscription_date' => request('subscription_date'), 'searchValue' => request('searchValue')])}}">
                            <img width="14" src="{{dynamicAsset(path: 'public/assets/back-end/img/excel.png')}}" class="excel" alt="">
                            <span class="ps-2">{{ translate('export') }}</span>
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};"
                        class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                        <thead class="thead-light thead-50 text-capitalize">
                        <tr>
                            <th>{{ translate('SL')}}</th>
                            <th scope="col">
                                {{ translate('email')}}
                            </th>
                            <th>{{ translate('subscription_date')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach ($subscriberList as $key=>$item)
                                <tr>
                                    <td>{{$subscriberList->firstItem()+$key}}</td>
                                    <td>{{$item->email}}</td>
                                    <td>
                                        {{date('d M Y, h:i A',strtotime($item->created_at))}}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                </div>

                <div class="table-responsive mt-4">
                    <div class="px-4 d-flex justify-content-lg-end">
                        {{$subscriberList->links()}}
                    </div>
                </div>
                @if(count($subscriberList)==0)
                    @include('layouts.back-end._empty-state',['text'=>'no_subscriber_found'],['image'=>'default'])
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
    <script type="text/javascript">
        changeInputTypeForDateRangePicker($('input[name="subscription_date"]'));
    </script>
@endpush
