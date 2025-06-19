<script>
    "use strict";
    $(document).ready(function () {
        $('.save-draft').on('click', function () {
            $('#status').val(0);
            $('#is_draft').val(1);
            $('#blog-ajax-form').submit();
        });

        $('.publish-blog').on('click', function () {
            $('#status').val(1);
            $('#is_draft').val(0);
            $('#blog-ajax-form').submit();
        });

        $('.clear-draft').on('click', function () {
            $('#clear_draft').val(1);
            $('#blog-ajax-form').submit();
        })

        $('.reset-form').on('click', function(){
            window.location.reload();
        })

        $(document).on('submit', '#blog-ajax-form', function (event) {
            event.preventDefault();
            let form = document.getElementById('blog-ajax-form');
            let formData = new FormData(form);

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr("content")
                },
                beforeSend: function () {
                    $('.publish-blog').attr('disabled', true);
                    $('.save-draft').attr('disabled', true);
                },
                success: function (response) {
                    if (response.errors) {
                        for (let i = 0; i < response.errors.length; i++) {
                            toastr.error(response.errors[i].message, {
                                CloseButton: true,
                                ProgressBar: true,
                            });
                        }
                    }

                    if (response?.status?.toString() === '1') {
                        toastr.success(response?.message);
                        if (response?.redirect) {
                            location.href = response?.redirect;
                        }
                    }
                },
                complete: function () {
                    $('.publish-blog').removeAttr('disabled');
                    $('.save-draft').removeAttr('disabled');
                },
                error: function (response) {
                    $('.save-draft').removeAttr('disabled');
                }
            });
        });

        let deleteFileInput = $(".delete_file_input");
        let elementCustomUploadInputFileByID = $(".custom-upload-input-file");

        if(deleteFileInput.length > 0){
            $(".delete_file_input").on("click", function () {
                let $parentDiv = $(this).parent().parent();
                $parentDiv.find('input[type="file"]').val("");
                $parentDiv.find(".img_area_with_preview img").addClass("d-none");
                $(this).removeClass("d-flex");
                $(this).hide();
            });
        }

        if (elementCustomUploadInputFileByID.length > 0) {
            elementCustomUploadInputFileByID.on("change", function () {
                if (parseFloat($(this).prop("files").length) !== 0)     {
                    let parentDiv = $(this).closest("div");
                    parentDiv.find(".delete_file_input").fadeIn();
                }
            });
            function uploadColorImage(thisData = null) {
                if (thisData) {
                    document
                        .getElementById(thisData.dataset.imgpreview)
                        .setAttribute("src", window.URL.createObjectURL(thisData.files[0]));
                    document
                        .getElementById(thisData.dataset.imgpreview)
                        .classList.remove("d-none");

                    try {
                        if (
                            thisData.dataset.imgpreview == "pre_img_viewer" &&
                            !$("#meta_image_input").val()
                        ) {
                            $("#pre_meta_image_viewer").removeClass("d-none");
                            $(".pre-meta-image-viewer").attr(
                                "src",
                                window.URL.createObjectURL(thisData.files[0])
                            );
                        }
                    } catch (e) {}
                }
            }

            $(".action-upload-color-image").on("change", function () {
                uploadColorImage(this);
            });
        }
    });

    $(document).on('click', '.blog-section-temp-view', function (e) {
        e.preventDefault();
        let form = document.getElementById('blog-ajax-form');
        let formData = new FormData(form);
        $.ajax({
            url: $(this).data('url'),
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr("content")
            },
            success: function (response) {
                if (response?.route) {
                    window.open(response.route, '_blank');
                }
            },
            error: function (response) {
            }
        });
    });
</script>
