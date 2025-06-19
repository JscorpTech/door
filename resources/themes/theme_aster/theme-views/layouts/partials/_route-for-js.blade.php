<span id="update_nav_cart_url" data-url="{{route('cart.nav-cart')}}"></span>
<span id="remove_from_cart_url" data-url="{{ route('cart.remove') }}"></span>
<span id="update-quantity-basic-url" data-url="{{route('cart.updateQuantity')}}"></span>
<span id="route-cart-variant-price" data-url="{{ route('cart.variant_price') }}"></span>
<span id="route-checkout-details" data-url="{{ route('checkout-details') }}"></span>
<span id="route-checkout-payment" data-url="{{ route('checkout-payment') }}"></span>
<span id="order_note_url" data-url="{{ route('order_note') }}"></span>
<span id="update_quantity_url" data-url="{{route('cart.updateQuantity.guest')}}"></span>
<span id="get-place-holder-image" data-src="{{ theme_asset('assets/img/image-place-holder.png') }}"></span>
<span id="authentication-status" data-auth="{{ auth('customer')->check() ? 'true' : 'false' }}"></span>
<span id="set-shipping-url" data-url="{{url('/')}}/customer/set-shipping-method"></span>
<span id="digital-product-download-otp-reset" data-route="{{ route('digital-product-download-otp-reset') }}"></span>
<span id="order_again_url" data-action="{{ route('cart.order-again') }}"></span>
<span id="route-product-restock-request" data-url="{{ route('cart.product-restock-request') }}"></span>
<span id="route-get-session-recaptcha-code"
      data-route="{{ route('get-session-recaptcha-code') }}"
      data-mode="{{ env('APP_MODE') }}"
></span>
