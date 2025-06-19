@extends('layouts.front-end.app')

@section('title',translate('shipping_policy'))

@section('content')
    <div class="container py-5 rtl text-align-direction">
        <h2 class="text-center mb-3 headerTitle">{{ translate('shipping_policy') }}</h2>
        <div class="card __card">
            <div class="card-body text-justify">
                {!! $shippingPolicy['content'] !!}
            </div>
        </div>
    </div>
@endsection
