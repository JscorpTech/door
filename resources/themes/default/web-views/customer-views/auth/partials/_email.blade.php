<div class="form-group">
    <label class="form-label font-semibold">
        {{ translate('email') }} / {{ translate('phone')}}
        <span class="input-required-icon">*</span>
    </label>
    <input class="form-control text-align-direction auth-email-input" type="text" name="user_identity" id="si-email"
           value="{{old('user_identity')}}" placeholder="{{ translate('enter_email_or_phone') }}"
           required>
    <div class="invalid-feedback">{{ translate('please_provide_valid_email_or_phone_number') }} .</div>
</div>
