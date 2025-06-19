<div class="form-group mb-2">
    <label for="email">{{ translate('email') }} / {{ translate('phone') }}</label>
    <input
        name="user_identity" id="si-email"
        class="form-control auth-email-input" value="{{old('user_identity')}}"
        placeholder="{{translate('enter_email_or_phone_number')}}" required
    />
</div>
