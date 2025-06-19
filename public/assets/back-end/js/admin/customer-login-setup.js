function ajaxSetupToken() {
    $.ajaxSetup({
        headers: {
            "X-XSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                "content"
            ),
        },
    });
}

$('.social-media-status-checkbox').on('click', function (event) {
    let checkbox = $(this);
    if (checkbox.prop('checked')) {
        event.preventDefault();
        ajaxSetupToken();
        $.post({
            url: checkbox.data("route"),
            data: {
                _token: $('meta[name="csrf-token"]').attr("content"),
                key: checkbox.data("key")
            },
            beforeSend: function () {
                // $("#loading").fadeIn();
            },
            success: function (response) {
                if (response?.status === 0) {
                    $('#customerLoginConfigValidation .modal-body').empty().html(response?.htmlView);
                    $('#customerLoginConfigValidation').modal('show');
                } else {
                    checkbox.prop('checked', !checkbox.prop('checked'));
                }
            },
            complete: function () {
                // $("#loading").fadeOut();
            },
        });
    }
});

$('#customer-login-setup-update').on('submit', function(event) {
    let manualLogin = $('#customer-manual-login').prop('checked');
    let otpLogin = $('#customer-otp-login').prop('checked');
    let socialMediaLogin = $('#customer-social-login').prop('checked');

    if (!manualLogin && !otpLogin && !socialMediaLogin) {
        event.preventDefault();
        let customerLoginSetupValidationMsg = $('#customer-login-setup-validation-msg');
        Swal.fire({
            type: 'warning',
            title: customerLoginSetupValidationMsg.data('title'),
            text: customerLoginSetupValidationMsg.data('text'),
            confirmButtonText: customerLoginSetupValidationMsg.data('ok'),
            confirmButtonColor: '#FC6A57',
        });
    }

    if (
        socialMediaLogin &&
        (manualLogin || otpLogin ) &&
        !$('#google_login').prop('checked') &&
        !$('#apple_login').prop('checked') &&
        !$('#facebook_login').prop('checked')) {
        event.preventDefault();
        let customerLoginSetupValidationMsg = $('#customer-login-setup-validation-msg');
        Swal.fire({
            type: 'warning',
            title: customerLoginSetupValidationMsg.data('title'),
            text: $('.select-google-or-facebook').data('text-two'),
            confirmButtonText: customerLoginSetupValidationMsg.data('ok'),
            confirmButtonColor: '#FC6A57',
        });
        return false;
    }

    if (!selectGoogleOrFacebook()) {
        event.preventDefault();
        let customerLoginSetupValidationMsg = $('#customer-login-setup-validation-msg');
        Swal.fire({
            type: 'warning',
            title: customerLoginSetupValidationMsg.data('title'),
            text: $('.select-google-or-facebook').data('text'),
            confirmButtonText: customerLoginSetupValidationMsg.data('ok'),
            confirmButtonColor: '#FC6A57',
        });
    }
});

$('#customer-social-login').on('click', () => {
    selectGoogleOrFacebook();
    showSocialMediaLoginSection();
});
$('#google_login').on('click', () => {selectGoogleOrFacebook();});
$('#facebook_login').on('click', () => {selectGoogleOrFacebook();});

function selectGoogleOrFacebook() {
    let customerSocialLoginCheckBox =  $('#customer-social-login');
    let customerGoogleLoginCheckBox =  $('#google_login');
    let customerFacebookLoginCheckBox =  $('#facebook_login');
    let customerAppleLoginCheckBox =  $('#apple_login');
    let customerManualLoginCheckBox =  $('#customer-manual-login');
    let customerOTPLoginCheckBox =  $('#customer-otp-login');

    if (
        (
            customerSocialLoginCheckBox.prop('checked') &&
            !customerManualLoginCheckBox.prop('checked') &&
            !customerOTPLoginCheckBox.prop('checked') &&
            !customerGoogleLoginCheckBox.prop('checked') &&
            !customerFacebookLoginCheckBox.prop('checked')
        ) || (
            (customerManualLoginCheckBox.prop('checked') || customerOTPLoginCheckBox.prop('checked')) &&
            customerSocialLoginCheckBox.prop('checked') &&
            !customerGoogleLoginCheckBox.prop('checked') &&
            !customerAppleLoginCheckBox.prop('checked') &&
            !customerFacebookLoginCheckBox.prop('checked')
        )
    ) {
        return false;
    }
    return true;
}

function showSocialMediaLoginSection() {
    let customerSocialLoginContainer =  $('.social-media-login-container');
    let customerGoogleLoginCheckBox =  $('#google_login');
    let customerFacebookLoginCheckBox =  $('#facebook_login');
    let customerAppleLoginCheckBox =  $('#apple_login');
    if ($('#customer-social-login').prop('checked')) {
        customerSocialLoginContainer.slideDown();
        if (customerGoogleLoginCheckBox.data('status')?.toString() === 'true') {
            customerGoogleLoginCheckBox.prop('checked', true)
        }
        if (customerFacebookLoginCheckBox.data('status')?.toString() === 'true') {
            customerFacebookLoginCheckBox.prop('checked', true)
        }
        if (customerAppleLoginCheckBox.data('status')?.toString() === 'true') {
            customerAppleLoginCheckBox.prop('checked', true)
        }
    } else {
        customerSocialLoginContainer.slideUp();
        customerGoogleLoginCheckBox.prop('checked', false)
        customerFacebookLoginCheckBox.prop('checked', false)
        customerAppleLoginCheckBox.prop('checked', false)
    }
}
