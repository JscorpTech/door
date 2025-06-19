$(document).ready(function () {

    $('.quill-editor').each(function (index) {
        var associatedTextarea = $(this).siblings('textarea');

        var quillEditor = new Quill(this, {
            theme: 'snow',
            modules: {
                toolbar: {
                    container: [
                        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'header': 1 }, { 'header': 2 }],
                        [{ 'list': 'ordered' }, { 'list': 'bullet' }, { 'list': 'check' }],
                        [{ 'align': [] }],
                        [{ 'font': [] }],
                        [{ 'size': ['small', false, 'large', 'huge'] }],
                        [{ 'color': [] }, { 'background': [] }],
                        ['blockquote', 'code-block'],
                        ['link', 'image', 'video', 'formula'],
                        [{ 'script': 'sub' }, { 'script': 'super' }],
                        [{ 'indent': '-1' }, { 'indent': '+1' }],
                        [{ 'direction': 'rtl' }],
                    ],
                    handlers: {
                        image: function () {
                            var range = this.quill.getSelection();
                            var input = document.createElement('input');
                            input.setAttribute('type', 'file');
                            input.setAttribute('accept', 'image/*');
                            input.click();
                            input.onchange = () => {
                                var file = input.files[0];
                                if (file) {
                                    var reader = new FileReader();
                                    reader.onload = () => {
                                        var base64Image = reader.result;
                                        // Open the modal to ask for alt text
                                        openAltTextModal(base64Image, range.index, this.quill, 'image');
                                    };
                                    reader.readAsDataURL(file);
                                }
                            };
                        },
                        video: function () {
                            var range = this.quill.getSelection();
                            openVideoModal(range.index, this.quill);
                        }
                    }
                }
            }
        });

        // Update hidden textarea on text change
        quillEditor.on('text-change', function () {
            associatedTextarea.val(quillEditor.root.innerHTML);
        });

        // Store the Quill instance on the element for later use
        $(this).data('quill', quillEditor);
    });

// Function to open the alt text modal for image
    function openAltTextModal(mediaSource, index, quill, type) {
        var modalHTML = `
            <div class="alt-text-modal-overlay">
                <div class="alt-text-modal">
                    <h2>Enter Alt Text for the ${type === 'image' ? 'Image' : 'Video'}</h2>
                    <input type="text" id="alt-text-input" placeholder="Enter alt text...">
                    <button id="save-alt-text-btn">Save</button>
                    <button id="cancel-alt-text-btn">Cancel</button>
                </div>
            </div>
        `;

        // Append modal to the body
        $('body').append(modalHTML);

        // Save button click event
        $('#save-alt-text-btn').on('click', function () {
            var altText = $('#alt-text-input').val();
            if (type === 'image') {
                quill.insertEmbed(index, 'image', mediaSource, Quill.sources.USER);
                var img = quill.root.querySelector(`img[src="${mediaSource}"]`);
                if (img) {
                    img.setAttribute('alt', altText); // Set alt text
                }
            } else if (type === 'video') {
                var videoUrl = mediaSource;
                var iframe = `https://www.youtube.com/embed/${videoUrl}`;
                quill.insertEmbed(index, 'video', iframe, Quill.sources.USER);
                var iframeElement = quill.root.querySelector(`iframe[src="https://www.youtube.com/embed/${videoUrl}"]`);
                if (iframeElement) {
                    iframeElement.setAttribute('alt', altText || ''); // Set alt text for video
                }
            }
            closeAltTextModal();
        });

        // Cancel button click event
        $('#cancel-alt-text-btn').on('click', function () {
            closeAltTextModal();
        });
    }

// Function to close the modal
    function closeAltTextModal() {
        $('.alt-text-modal-overlay').remove();
    }

// Open YouTube video modal
    function openVideoModal(index, quill) {
        var modalHTML = `
            <div class="video-url-modal-overlay">
                <div class="video-url-modal">
                    <h2>Enter YouTube Video URL</h2>
                    <input type="text" id="video-url-input" placeholder="Enter YouTube URL...">
                    <button id="save-video-url-btn">Save</button>
                    <button id="cancel-video-url-btn">Cancel</button>
                </div>
            </div>
        `;

        // Append modal to the body
        $('body').append(modalHTML);

        // Save button click event for video
        $('#save-video-url-btn').on('click', function () {
            var videoUrl = $('#video-url-input').val();
            var videoId = extractYouTubeID(videoUrl);
            if (videoId) {
                // Create the iframe element
                var iframeElement = `https://www.youtube.com/embed/${videoId}`;
                // Insert the video into the Quill editor
                quill.insertEmbed(index, 'video', iframeElement, Quill.sources.USER);
            } else {
                alert('Invalid YouTube URL');
            }
            closeVideoModal();
        });

        // Cancel button click event
        $('#cancel-video-url-btn').on('click', function () {
            closeVideoModal();
        });
    }


    // Function to close the video modal
    function closeVideoModal() {
        $('.video-url-modal-overlay').remove();
    }

    // Function to extract YouTube video ID from URL
    function extractYouTubeID(url) {
        var match = url.match(/(?:youtube\.com\/(?:[^/]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
        return match ? match[1] : null;
    }

});
