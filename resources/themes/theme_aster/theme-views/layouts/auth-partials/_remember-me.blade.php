@php($rememberId = rand(111, 999))
<div class="d-flex justify-content-between gap-3 align-items-center mb-2">
    <label for="remember{{ $rememberId }}" class="d-flex gap-1 align-items-center mb-0">
        <input type="checkbox" name="remember"
               id="remember{{ $rememberId }}" {{ old('remember') ? 'checked' : '' }}/>
        {{ translate('remember_me') }}
    </label>

    @if(isset($forgotPassword) && $forgotPassword)
        <a href="{{route('customer.auth.recover-password')}}" class="text-capitalize">
            {{ translate('forgot_password').'?' }}
        </a>
    @endif
</div>
