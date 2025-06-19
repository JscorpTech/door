"use strict";

$(document).ready(function () {
    $("#myInput").on("keyup", function (e) {
        var value = $(this).val().toLowerCase();
        $(".list_filter").filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    $("#chat-search-form").on("submit", function (e) {
        e.preventDefault();
    });

    imageSlider();
    toggleVideo();
    namePdf();
    manipulateTooltip();
    downloadZip();
    ajaxFormRenderChattingMessages();

    function ajaxFormRenderChattingMessages() {
        scrollToBottom();
        $(".chatting-messages-form").on("submit", function (event) {
            event.preventDefault();
            let userId = $(".get-ajax-message-view.active").data("user-id");
            let actionURL = $("#chatting-post-url").data("url") + userId;
            let totalFilesCount = selectedFiles?.length + selectedImages?.length;
            let formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    "X-XSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            });
            $.ajax({
                type: "POST",
                url: actionURL,
                data: formData,
                processData: false,
                contentType: false,
                xhr: function () {
                    var xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener(
                        "progress",
                        function (evt) {
                            if (evt.lengthComputable) {
                                var percentComplete =
                                    (evt.loaded / evt.total) * 100;
                                $(".circle-progress").show();
                                $(".circle-progress")
                                    .find(".text")
                                    .text(
                                        `Uploading ${totalFilesCount} files`
                                    );
                                $(".circle-progress")
                                    .find("#bar")
                                    .attr(
                                        "stroke-dashoffset",
                                        100 - percentComplete
                                    );
                            }
                        },
                        false
                    );
                    return xhr;
                },
                beforeSend: function () {
                    $("#message-send-button").attr("disabled", true);
                },
                success: function (response) {
                    $("#chatting-messages-section").html(response.chattingMessages);
                    $("#msgInputValue").val("");
                    $(".image-array").empty();
                    $(".file-array").empty();
                    let container = document.getElementById(
                        "selected-files-container"
                    );
                    let containerImage = document.getElementById(
                        "selected-media-container"
                    );
                    container.innerHTML = "";
                    containerImage.innerHTML = "";
                    selectedFiles = [];
                    selectedImages = [];
                    scrollToBottom();
                    imageSlider();
                    toggleVideo();
                    downloadZip();
                    namePdf();
                    manipulateTooltip();

                    if (response.errors) {
                        for (
                            let index = 0;
                            index < response.errors.length;
                            index++
                        ) {
                            toastr.error(response.errors[index].message);
                        }
                    } else if (response.error) {
                        toastr.error(response.error);
                    }

                    setTimeout(() => {
                        $(".circle-progress").find(".text").text(`Uploaded ${totalFilesCount} files`);
                        $(".circle-progress").hide();
                    }, 1000)
                },
                complete: function () {
                    $(".circle-progress").hide();
                    $("#message-send-button").removeAttr("disabled");
                    $('[data-bs-toggle="tooltip"]').tooltip();

                    setTimeout(() => {
                        $(".circle-progress").find(".text").text(`Uploaded ${totalFilesCount} files`);
                        $(".circle-progress").hide();
                    }, 1000)
                },
                error: function (error) {
                    if (error.status === 413) {
                        toastr.warning($('#message-media-error').data("text"));
                    } else {
                        try {
                            const errorData = JSON.parse(error.responseText);
                            if (errorData.message) {
                                toastr.warning(errorData.message);
                            } else {
                                toastr.warning("An unknown error occurred.");
                            }
                        } catch (e) {
                            toastr.error($('#message-media-error').data("text"));
                            console.error("Error parsing response:", e, error);
                        }
                    }

                    setTimeout(() => {
                        $(".circle-progress").find(".text").text(`Uploaded ${totalFilesCount} files`);
                        $(".circle-progress").hide();
                    }, 1000)
                },
            });
        });
    }

    function scrollToBottom() {
        try {
            $(".scroll_msg")
                .stop()
                .animate({scrollTop: $(".scroll_msg")[0].scrollHeight}, 1000);
        } catch (e) {
        }
    }

    function imageSlider() {
        // Set specific slide when modal is shown
        $('[data-bs-target^="#imgViewModal"]').on("click", function () {
            var modalId = $(this).data("bs-target");
            var $carousel = $(modalId).find(".imgView-slider");

            // Count slides in this specific modal
            var slideCount = $(modalId).find('.imgView-item').length;

            // Destroy any previous Owl Carousel instance
            if ($carousel.hasClass("owl-loaded")) {
                $carousel.trigger("destroy.owl.carousel").removeClass("owl-loaded");
                $carousel.html($carousel.find(".owl-stage-outer").html());
            }

            // If only one slide, initialize without carousel features
            if (slideCount <= 1) {
                $(modalId).find(".imgView-owl-prev, .imgView-owl-next").hide();
                var imgView = $carousel.owlCarousel({
                    items: 1,
                    loop: false,
                    margin: 0,
                    nav: false,
                    dots: false,
                    mouseDrag: false,
                    touchDrag: false,
                    autoplay: false,
                    smartSpeed: 500
                });
                return;
            }

            // Initialize Owl Carousel for multiple slides
            var imgView = $carousel.owlCarousel({
                items: 1,
                loop: false,
                margin: 0,
                nav: false,
                dots: false,
                mouseDrag: true,
                touchDrag: true,
                autoplay: false,
                smartSpeed: 500,
                onChanged: function(event) {
                    // Get current item index
                    var currentIndex = event.item.index;

                    // Hide/show prev button
                    if (currentIndex === 0) {
                        $(modalId).find(".imgView-owl-prev").prop('disabled', true).addClass('disabled');
                    } else {
                        $(modalId).find(".imgView-owl-prev").prop('disabled', false).removeClass('disabled');
                    }

                    // Hide/show next button
                    if (currentIndex === slideCount - 1) {
                        $(modalId).find(".imgView-owl-next").prop('disabled', true).addClass('disabled');
                    } else {
                        $(modalId).find(".imgView-owl-next").prop('disabled', false).removeClass('disabled');
                    }
                }
            });

            // Show navigation buttons
            $(modalId).find(".imgView-owl-prev, .imgView-owl-next").show();

            // Set the specific slide
            var index = $(this).data("index");
            imgView.trigger("to.owl.carousel", [index, 0]);

            // Navigation button click handlers
            $(modalId).find(".imgView-owl-prev").on("click", function () {
                imgView.trigger("prev.owl.carousel");
            });

            $(modalId).find(".imgView-owl-next").on("click", function () {
                imgView.trigger("next.owl.carousel");
            });
        });
    }


    function toggleVideo() {
        $(".modal_video-play-btn").on("click", function () {
            const videoElement = $(this).siblings("video")[0];

            if (videoElement) {
                videoElement.controls = true;
                videoElement.play();
            }

            $(this).remove();
        });

        $("video").on("play", function () {
            $(this).siblings(".modal_video-play-btn").remove();
        });

        $("video").on("pause", function () {
            const videoElement = this;
            if (!$(videoElement).siblings(".modal_video-play-btn").length) {
                const iconSrc = $("#get-video-preview-icon").data("icon");
                const playButton = $("<button>", {
                    type: "button",
                    class: "btn video-play-btn modal_video-play-btn p-1",
                    html: `<img src="${iconSrc}" alt="Play">`,
                });

                playButton.insertAfter(videoElement);

                playButton.on("click", function () {
                    videoElement.controls = true;
                    videoElement.play();
                    $(this).remove();
                });
            }
        });
    }


    function downloadZip() {
        $('.zip-download').on("click", function (event) {
            event.preventDefault();

            const zipWrapper = $(this).closest(".zip-wrapper").find(".zip-images");
            if (!zipWrapper.length) {
                console.error("No .zip-images container found.");
                return;
            }

            const zip = new JSZip();
            const zipFolder = zip.folder("files");
            const images = zipWrapper.find("img");
            const videos = zipWrapper.find("video");

            if (images.length === 0 && videos.length === 0) {
                console.error("No images or videos found to zip.");
                return;
            }

            // Fetch all images and videos and zip them
            const mediaPromises = [];

            images.each((index, img) => {
                const imgUrl = $(img).attr("src");
                const filename = `image_${index + 1}.png`;

                mediaPromises.push(
                    fetch(imgUrl)
                        .then((response) => {
                            if (!response.ok) {
                                throw new Error(`Failed to fetch image: ${imgUrl}`);
                            }
                            return response.blob();
                        })
                        .then((blob) => zipFolder.file(filename, blob))
                        .catch((error) =>
                            console.error(
                                `Error fetching image (${filename}):`,
                                error
                            )
                        )
                );
            });

            videos.each((index, video) => {
                const videoUrl = $(video).find("source").first().attr("src");
                const filename = `video_${index + 1}.mp4`;

                if (videoUrl) {
                    mediaPromises.push(
                        fetch(videoUrl)
                            .then((response) => {
                                if (!response.ok) {
                                    throw new Error(
                                        `Failed to fetch video: ${videoUrl}`
                                    );
                                }
                                return response.blob();
                            })
                            .then((blob) => zipFolder.file(filename, blob))
                            .catch((error) =>
                                console.error(
                                    `Error fetching video (${filename}):`,
                                    error
                                )
                            )
                    );
                }
            });

            Promise.all(mediaPromises)
                .then(() => zip.generateAsync({type: "blob"}))
                .then((content) => saveAs(content, "files.zip"))
                .catch((error) => console.error("Error generating ZIP:", error));
        });
    }


    function namePdf() {
        $(".pdf-file-name").each(function () {
            const text = $(this).text().trim();
            const lastDotIndex = text.lastIndexOf(".");

            if (lastDotIndex !== -1) {
                const namePart = text.slice(0, lastDotIndex);
                const extension = text.slice(lastDotIndex);
                const maxLength = 20;

                if (namePart.length > maxLength) {
                    const truncatedName =
                        namePart.slice(0, maxLength - extension.length - 3) +
                        "..." +
                        extension;
                    $(this).text(truncatedName);
                } else {
                    $(this).text(text);
                }
            }
        });
    }


    function manipulateTooltip() {
        // Dispose tooltips when the modal is shown
        $(document).on("show.bs.modal", ".imgViewModal", function () {
            const tooltipTriggerList = document.querySelectorAll(
                '[data-bs-toggle="tooltip"]'
            );
            tooltipTriggerList.forEach((tooltipTriggerEl) => {
                const tooltip = bootstrap.Tooltip.getInstance(tooltipTriggerEl);
                if (tooltip) {
                    tooltip.dispose();
                }
            });
        });

// Reinitialize tooltips when the modal is hidden
        $(document).on("hidden.bs.modal", ".imgViewModal", function () {
            const tooltipTriggerList = document.querySelectorAll(
                '[data-bs-toggle="tooltip"]'
            );
            tooltipTriggerList.forEach((tooltipTriggerEl) => {
                new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });


        const tooltipTriggerList = document.querySelectorAll(
            '[data-bs-toggle="tooltip"]'
        );
        tooltipTriggerList.forEach((tooltipTriggerEl) => {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });

    }

    $(".get-ajax-message-view").on("click", function () {
        $(".get-ajax-message-view").removeClass("active");
        $(this).addClass("active");
        let userId = $(this).data("user-id");
        let actionURL = $("#chatting-post-url").data("url") + userId;
        $("#count-unread-messages-" + userId).remove();
        $(".get-ajax-message-view").find(".chat-people-name h6").addClass("fw-normal");
        $(this).find(".chat-people-name h6").removeClass("fw-normal");

        $.ajaxSetup({
            headers: {
                "X-XSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        });
        $.ajax({
            url: actionURL,
            type: "GET",
            beforeSend: function () {
                $("#loading").addClass("d-grid");
            },
            success: function (response) {
                if (response.userData) {
                    $("#chatting-messages-section").empty().html(response.chattingMessages);
                    $(".profile-image").attr("src", response.userData.image);
                    $(".profile-name").html(response.userData.name);
                    $("#profile_phone").html(response.userData.phone);
                    if (
                        parseInt(response.userData["temporary-close-status"]) === 1
                    ) {
                        $(".temporarily-closed-sticky-alert")
                            .removeClass("d-none")
                            .css({
                                display: "",
                            });
                    } else {
                        $(".temporarily-closed-sticky-alert")
                            .addClass("d-none")
                            .css({
                                display: "none",
                            });
                    }
                    $("#current-user-hidden-id").val(userId);
                    $(".get-ajax-message-view.active")[0].scrollIntoView({
                        behavior: "auto",
                        block: "nearest",
                        inline: "center",
                    });
                    scrollToBottom();
                    imageSlider();
                    toggleVideo();
                    downloadZip();
                    namePdf();
                    manipulateTooltip();
                }
            },
            complete: function () {
                $("#loading").removeClass("d-grid");
                $('[data-bs-toggle="tooltip"]').tooltip();
            },
        });
    });

});

