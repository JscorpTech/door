"use strict";
let selectedImages = [];
let selectedMedia = [];
$(document).ready(function () {

    let exceeds10MBSizeLimit = $("#exceeds10MBSizeLimit").data('text') ?? 'File exceeds 10MB size limit';

    $("#select-media").on("change", function () {
        const maxFileSize = 10 * 1024 * 1024; // 10MB in bytes

        for (let index = 0; index < this.files.length; ++index) {
            const file = this.files[index];
            if (file.size > maxFileSize) {
                toastr.error(`"${file.name}" ` + exceeds10MBSizeLimit);
                continue;
            }
            selectedImages.push(file);
        }
        displaySelectedImages();
        this.value = null;
    });

    function displaySelectedImages() {
        const containerImage = document.getElementById(
            "selected-media-container"
        );
        containerImage.innerHTML = "";
        selectedImages.forEach((file, index) => {
            const input = document.createElement("input");
            input.type = "file";
            input.name = `media[${index}]`;
            input.classList.add(`image-index${index}`);
            input.hidden = true;
            containerImage.appendChild(input);
            const blob = new Blob([file], { type: file.type });
            const file_obj = new File([file], file.name);
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file_obj);
            input.files = dataTransfer.files;
        });
        let imageArray = $(".image-array");
        imageArray.empty();
        for (let index = 0; index < selectedImages.length; ++index) {
            let fileReader = new FileReader();

            fileReader.onload = function () {
                let $uploadDiv;

                if (this.result.includes("video/")) {
                    const mimeType = this.result.split(";")[0].split(":")[1];
                    $uploadDiv = jQuery.parseHTML(`
                        <div class='upload_img_box upload_video_box'>
                            <span class='img-clear'><i class='bi bi-x'></i></span>
                            <div class="position-relative">
                                <video class="rounded video-element" width="80" height="45" preload="metadata">
                                    <source src="" type="${mimeType}">
                                    <source src="" type="video/ogg">
                                    Your browser does not support the video tag.
                                </video>
                                <button type="button" class="btn video-play-btn text--primary rounded-circle bg-white p-1 d-flex justify-content-center align-items-center">
                                    <i class="bi bi-play-fill"></i>
                                </button>
                            </div>
                        </div>
                    `);
                    $($uploadDiv).find("video source").attr("src", this.result);
                } else {
                    $uploadDiv = jQuery.parseHTML(`
                        <div class='upload_img_box'>
                            <span class='img-clear'><i class='bi bi-x'></i></span>
                            <img class="rounded" src='' alt=''>
                        </div>
                    `);
                    $($uploadDiv).find("img").attr("src", this.result);
                }

                imageArray.append($uploadDiv);

                // Handle the clear functionality
                $($uploadDiv)
                    .find(".img-clear")
                    .on("click", function () {
                        // Remove the image box from the DOM
                        $(this).closest(".upload_img_box").remove();

                        // Find and remove the file from selectedImages array
                        const fileToRemove = selectedImages.find(
                            (file) =>
                                file.name === file.name &&
                                file.size === file.size
                        );
                        selectedImages = selectedImages.filter(
                            (file) => file !== fileToRemove
                        );

                        // Update the input field associated with this file
                        $(".image-index" + index).remove();
                    });
            };

            // Start reading the file
            fileReader.readAsDataURL(selectedImages[index]);
        }
    }
});
