"use strict";

$(document).on('ready', function () {
    $('.js-toggle-password').each(function () {
        new HSTogglePassword(this).init()
    });
    $('.js-validate').each(function () {
        $.HSCore.components.HSValidation.init($(this));
    });
});

$('.submit-login-form').on('click',function (){
    var response = 1;
    try{
        response = grecaptcha.getResponse();
    }catch (e) {

    }
    if (response.length === 0) {
        e.preventDefault();
        toastr.error($('#message-please-check-recaptcha').data('text'));
    }else {
        $.ajaxSetup({
            headers: {
                'X-XSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.post({
            url: $('#vendor-login-form').attr('action'),
            data: $('#vendor-login-form').serialize(),
            beforeSend: function () {
                $('#loading').fadeIn();
            },
            success: function (data) {
                if (data.errors) {
                    for (let index = 0; index < data.errors.length; index++) {
                        toastr.error(data.errors[index].message, {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }
                } else if(data.error){
                    toastr.error(data.error, {
                        CloseButton: true,
                        ProgressBar: true
                    });
                } else if(data.status){
                    $('.'+data.status+'-message').removeClass('d-none')
                } else {
                    location.href = data.redirectRoute;
                    toastr.success(data.success)
                }

                if (data.errors) {
                    for (
                        let index = 0;
                        index < data.errors.length;
                        index++
                    ) {
                        toastr.error(data.errors[index].message, {
                            CloseButton: true,
                            ProgressBar: true,
                        });
                    }
                }
            },
            complete: function () {
                $('#loading').fadeOut();
            },
            error: function (xhr) {
                if (xhr.responseJSON) {
                    const responseErrors = xhr.responseJSON.errors;
                    if (Array.isArray(responseErrors)) {
                        responseErrors.forEach(error => {
                            toastr.error(error.message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        });
                    } else if (typeof responseErrors === 'object') {
                        for (const key in responseErrors) {
                            if (responseErrors.hasOwnProperty(key)) {
                                toastr.error(responseErrors[key], {
                                    CloseButton: true,
                                    ProgressBar: true
                                });
                            }
                        }
                    } else if (xhr.responseJSON.error) {
                        toastr.error(xhr.responseJSON.error, {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    } else {
                        toastr.error('An unexpected error occurred. Please try again.', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }
                } else {
                    toastr.error('An unknown error occurred.', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }

                setTimeout(() => {
                    location.reload();
                }, 3000)
            }
        })
    }
})

$('.clear-alter-message').on('click',function (){
    $('.vendor-suspend').addClass('d-none')
})
$('.get-login-recaptcha-verify').on('click', function () {
    document.getElementById('default_recaptcha_id').src = $(this).data('link') + "/" + Math.random()+'?captcha_session_id=vendorRecaptchaSessionKey';
});

$('#copyLoginInfo').on('click', function () {
    let vendorEmail = $('#vendor-email').data('email');
    let vendorPassword = $('#vendor-password').data('password');
    $('#signingVendorEmail').val(vendorEmail);
    $('#signingVendorPassword').val(vendorPassword);
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
