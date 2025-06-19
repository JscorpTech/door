"use strict";

$(document).on('ready', function () {
    // INITIALIZATION OF SHOW PASSWORD
    // =======================================================
    $('.js-toggle-password').each(function () {
        new HSTogglePassword(this).init()
    });

    // INITIALIZATION OF FORM VALIDATION
    // =======================================================
    $('.js-validate').each(function () {
        $.HSCore.components.HSValidation.init($(this));
    });
});

$("#admin-login-form").on('submit', function (e) {
    var response = grecaptcha.getResponse();
    if (response.length === 0) {
        e.preventDefault();
        toastr.error($('#message-please-check-recaptcha').data('text'));
    }
})

$('.get-login-recaptcha-verify').on('click', function () {
    let role = $('#role').val();
    document.getElementById('default_recaptcha_id').src = $(this).data('link') + "/" + Math.random()+"?captcha_session_id=default_recaptcha_id_"+role+"_login";
});

$('#copyLoginInfo').on('click', function () {
    let adminEmail = $('#admin-email').data('email');
    let adminPassword = $('#admin-password').data('password');
    $('#signingAdminEmail').val(adminEmail);
    $('#signingAdminPassword').val(adminPassword);
    toastr.success($('#message-copied_success').data('text'), 'Success!', {
        CloseButton: true,
        ProgressBar: true
    });
});

$('.onerror-logo').on('error', function () {
    let image = $('#onerror-logo').data('onerror-logo');
    $(this).attr('src', image);
});


function getSessionRecaptchaCode(sessionKey, inputSelector) {
    try {
        let routeGetSessionRecaptchaCode = $('#route-get-session-recaptcha-code');
        if (routeGetSessionRecaptchaCode.data('mode').toString() === 'dev') {
            let string = '.';
            let intervalId = setInterval(() => {
                if (string === '......') {
                    string = '.';
                }
                string = string + '.';
                $(inputSelector).val(string);
            }, 100);

            setTimeout(() => {
                clearInterval(intervalId);
                $.ajaxSetup({
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="_token"]').attr("content"),
                    },
                });
                $.ajax({
                    type: "POST",
                    url: $('#route-get-session-recaptcha-code').data('route'),
                    data: {
                        _token: $('meta[name="_token"]').attr("content"),
                        sessionKey: sessionKey,
                    },
                    success: function (response) {
                        $(inputSelector).val(response?.code);
                    },
                });
            }, 1000);
        }
    } catch (e) {
        console.log(e);
    }
}

$('.get-session-recaptcha-auto-fill').each(function () {
    getSessionRecaptchaCode($(this).data('session'), $(this).data('input'))
});

$('.get-session-recaptcha-auto-fill').on('click', function () {
    getSessionRecaptchaCode($(this).data('session'), $(this).data('input'));
});
