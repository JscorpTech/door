'use strict';

let initialMaintenanceModeStatus = $('#maintenanceModeSwitch');
let maintenanceModeModalElement = $('#maintenance-mode-modal');
let maintenanceModeStatus = initialMaintenanceModeStatus.prop('checked');

$(document).ready(function () {

    maintenanceModeModalElement.on('show.bs.modal', function () {
        // $('#maintenance-mode-checkbox').prop('checked', parseFloat(initialMaintenanceModeStatus.is(':checked')));
    });

    maintenanceModeModalElement.on('hidden.bs.modal', function () {
        $('#maintenanceModeSwitch').prop('checked', $('#maintenanceModeSwitch').is(':checked'));
    });

    $('.maintenance-cancel-button').click(function () {
        if (maintenanceModeStatus?.toString() === 'false') {
            $('#maintenanceModeSwitch').prop('checked', false);
        }
        maintenanceModeModalElement.modal('hide');
    });

    $('#maintenance-mode-checkbox').change(function () {
        initialMaintenanceModeStatus.prop('checked', $(this).is(':checked'));
    });

    $('.maintenance-mode-show').click(function () {
        maintenanceModeModalElement.modal('show');
    });

    $('#advanceFeatureToggle').click(function (event) {
        event.preventDefault();
        $('#advanceFeatureSection').show();
        $('#advanceFeatureButtonDiv').hide();
    });

    $('#seeLessToggle').click(function (event) {
        event.preventDefault();
        $('#advanceFeatureSection').hide();
        $('#advanceFeatureButtonDiv').show();
    });

    $('#allSystemSelection').change(function () {
        var isChecked = $(this).is(':checked');
        $('.system-checkbox').prop('checked', isChecked);
    });

    // If any other checkbox is unchecked, also uncheck "All System"
    $('.system-checkbox').not('#allSystemSelection').change(function () {
        if (!$(this).is(':checked')) {
            $('#allSystemSelection').prop('checked', false);
        } else {
            // Check if all system-related checkboxes are checked
            if ($('.system-checkbox').not('#allSystemSelection').length === $('.system-checkbox:checked').not('#allSystemSelection').length) {
                $('#allSystemSelection').prop('checked', true);
            }
        }
    });

    var startDate = $('#startDate');
    var endDate = $('#endDate');
    var dateError = $('#dateError');

    function updateDatesBasedOnDuration(selectedOption) {
        if (selectedOption === 'one_day' || selectedOption === 'one_week') {
            var now = new Date();
            var timezoneOffset = now.getTimezoneOffset() * 60000;
            var formattedNow = new Date(now.getTime() - timezoneOffset).toISOString().slice(0, 16);

            if (selectedOption === 'one_day') {
                var end = new Date(now);
                end.setDate(end.getDate() + 1);
            } else if (selectedOption === 'one_week') {
                var end = new Date(now);
                end.setDate(end.getDate() + 7);
            }

            var formattedEnd = new Date(end.getTime() - timezoneOffset).toISOString().slice(0, 16);

            startDate.val(formattedNow).prop('readonly', false).prop('required', true);
            endDate.val(formattedEnd).prop('readonly', false).prop('required', true);
            $('.start-and-end-date').removeClass('opacity');
            dateError.hide();
        } else if (selectedOption === 'until_change') {
            startDate.val('').prop('readonly', true).prop('required', false);
            endDate.val('').prop('readonly', true).prop('required', false);
            $('.start-and-end-date').addClass('opacity');
            dateError.hide();
        } else if (selectedOption === 'customize') {
            startDate.prop('readonly', false).prop('required', true);
            endDate.prop('readonly', false).prop('required', true);
            $('.start-and-end-date').removeClass('opacity');
            dateError.hide();
        }
    }

    function validateDates() {
        var start = new Date(startDate.val());
        var end = new Date(endDate.val());
        if (start > end) {
            dateError.show();
            startDate.val('');
            endDate.val('');
        } else {
            dateError.hide();
        }
    }

    // Initial load
    var selectedOption = $('input[name="maintenance_duration"]:checked').val();
    updateDatesBasedOnDuration(selectedOption);

    // When maintenance duration changes
    $('input[name="maintenance_duration"]').change(function () {
        var selectedOption = $(this).val();
        updateDatesBasedOnDuration(selectedOption);
    });

    // When start date or end date changes
    $('#startDate, #endDate').change(function () {
        $('input[name="maintenance_duration"][value="customize"]').prop('checked', true);
        startDate.prop('readonly', false).prop('required', true);
        endDate.prop('readonly', false).prop('required', true);
        validateDates();
    });


    $('#maintenanceModeSwitch').on('click', function () {
        $('#maintenance-mode-checkbox').prop('checked', $(this).is(':checked'));
        if ($(this).data('status') == 'on') {
            if ($('#get-application-environment-mode').data('value') === 'demo') {
                callDemo()
                setTimeout(() => {
                    location.reload();
                }, 3000);
            } else {
                let getConfirmAndCancelButtonText = $('#get-confirm-and-cancel-button-text');
                Swal.fire({
                    title: getConfirmAndCancelButtonText.data('sure'),
                    text: $(this).data('warning'),
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    cancelButtonText: getConfirmAndCancelButtonText.data('cancel'),
                    confirmButtonText: getConfirmAndCancelButtonText.data('confirm'),
                    reverseButtons: true
                }).then((result) => {
                    if (result.value) {
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                            }
                        });
                        $.ajax({
                            url: $(this).data('route'),
                            type: 'POST',
                            data: $('#maintenance-mode-form').serialize(),
                            beforeSend: function () {
                                $('#loading').show();
                            },
                            success: function (data) {
                                toastr.success(data.message);
                                location.reload();
                            },
                            complete: function () {
                                $('#loading').hide();
                            },
                        });
                    } else {
                        $('#maintenance-mode-checkbox').prop('checked', $(this).is(':checked'));
                        $(this).prop('checked', !$(this).is(':checked'));
                    }
                });
            }
        }
    });

});
