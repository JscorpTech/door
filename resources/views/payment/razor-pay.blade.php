@extends('payment.layouts.master')

@section('content')
    <div>
        <h1 class="text-center">{{ "Please do not refresh this page..." }}</h1>
    </div>

    <form action="{!!route('razor-pay.payment',['payment_id'=>$data->id])!!}" id="form" method="POST">
    @csrf
        <script src="https://checkout.razorpay.com/v1/checkout.js"
                data-key="{{ config()->get('razor_config.api_key') }}"
                data-amount="{{round($data->payment_amount, 2)*100}}"
                data-buttontext="Pay {{ round($data->payment_amount, 2) . ' ' . $data->currency_code }}"
                data-name="{{ $business_name }}"
                data-description="{{$data->payment_amount}}"
                data-image="{{ $business_logo }}"
                data-prefill.name="{{$payer->name ?? ''}}"
                data-prefill.email="{{$payer->email ?? ''}}"
                data-prefill.contact="{{ $payer?->phone ?? '' }}"
                data-callback_url="{{ route('razor-pay.callback', ['payment_data' => base64_encode($data->id)]) }}"
                data-theme.color="#ff7529">
        </script>
        <button class="btn btn-block" id="pay-button" type="submit" style="display:none"></button>
        <button class="razorpay-cancel-button" type="button" id="cancel-button" onclick="handleCancel()">Cancel</button>
    </form>

    <script type="text/javascript">
        "use strict";
        document.addEventListener("DOMContentLoaded", function () {
            document.getElementById("pay-button").click();
        });
        function handleCancel() {
            window.location.href = '{{ route('razor-pay.cancel', ['payment_id' => $data->id]) }}';
        }
    </script>
@endsection

@push('script')
    <style>
        .razorpay-cancel-button {
            border: 1px solid #0000008c;
            border-radius: 2px;
            margin: 0;
            font-size: 16px;
            padding: .125rem 1rem;
        }
    </style>
@endpush
