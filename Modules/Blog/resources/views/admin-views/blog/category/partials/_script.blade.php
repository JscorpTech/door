<script>
    const blogCSRFToken = $('meta[name="csrf-token"]').attr("content");

    $('.category-sidebar-toggle').on('click', function () {
        const offCanvas = document.getElementById('category-off-canvas');
        const overlay = document.getElementById('offcanvas-overlay');
        offCanvas.classList.toggle('active');
        overlay.classList.toggle('active');
    })

    $(document).ready(function () {
        $('.single_file_input').on('change', function (event) {
            let file = event.target.files[0];
            let $card = $(event.target).closest('.upload-file');
            let $textbox = $card.find('.upload-file-textbox');
            let $imgElement = $card.find('.upload-file-img');
            let $editBtn = $card.find('.edit-btn');

            if (file) {
                let reader = new FileReader();
                reader.onload = function (e) {
                    $textbox.hide();
                    $imgElement.attr('src', e.target.result).show();
                    $editBtn.css('opacity', 1);
                };
                reader.readAsDataURL(file);
            }
        });

        $('.edit-btn').on('click', function () {
            let $card = $(this).closest('.upload-file');
            let $fileInput = $card.find('.single_file_input');
            $fileInput.click();
        });

        $(document).on('submit', '.category-form-submit', function (event) {
            event.preventDefault();
        })

        $(document).on('click', '#category-form-cancel-btn', function () {
            $('.category-edit-form').addClass('d--none');
            $('.category-create-form').find('.form-dynamic-language-tab').removeClass('active');
            $('.category-create-form').find('.form-dynamic-language-tab[data-lang="en"]').addClass('active');
            $('.category-create-form').removeClass('d--none');

            $('.category-create-form').find('.category-lang-tab').addClass('d-none');
            $('.category-create-form').find('.category-lang-tab[data-lang="en"]').removeClass('d-none');
        })

        $(document).on('click', '.category-form-submit-btn', function () {
            $.ajaxSetup({
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="_token"]').attr("content"),
                },
            });
            let formElement = $($(this).data('form'));
            let formType = $(this).data('type') ?? 'add';
            $('#datatableSearch').val('');

            $.ajax({
                url: $(this).data('route'),
                method: 'POST',
                data: formElement.serialize(),
                success: function (response) {
                    if (response.errors) {
                        for (let index = 0; index < response.errors.length; index++) {
                            toastr.error(response.errors[index].message, {
                                CloseButton: true,
                                ProgressBar: true,
                            });
                        }
                    } else if (response.error) {
                        toastr.error(response.error, {
                            CloseButton: true,
                            ProgressBar: true,
                        });
                    } else {
                        toastr.success(response.message);
                        $('#categories-table').html(response.html);
                    }
                    formElement.find('input').val('');
                    if (formType === 'update') {
                        $('.category-edit-form').addClass('d--none');
                        $('.category-create-form').removeClass('d--none');
                    }
                    updateBlogCategorySelectList();
                },
                error: function (response) {
                    if (response.status === 422) {
                        let errors = response.responseJSON.errors;
                        Object.keys(errors).forEach(function (key) {
                            toastr.error(errors[key][0]);
                        });
                    } else {
                        toastr.error(response.responseJSON.message || 'Something went wrong!');
                    }
                }
            });
        });

        $(document).on('click', '.edit-category-btn', function () {
            let categoryId = $(this).data('id');
            $.ajaxSetup({headers: {"X-XSRF-TOKEN": blogCSRFToken}});

            $.ajax({
                url: $(this).data('route'),
                method: 'post',
                data: {
                    _token: blogCSRFToken,
                    id: categoryId,
                },
                success: function (response) {
                    response.lang_data.forEach((item) => {
                        let inputField = $(`#edit-${item.locale}_category_name`);
                        if (inputField.length) {
                            inputField.val(item.value);
                        } else {
                            console.warn(`Input field for locale ${item.locale} not found.`);
                        }
                    });

                    $('#edit-category-id').val(response.data.id);
                    $('.category-edit-form').removeClass('d--none');
                    $('.category-create-form').addClass('d--none');

                    $('.category-edit-form').find('.form-dynamic-language-tab').removeClass('active');
                    $('.category-edit-form').find('.form-dynamic-language-tab[data-lang="en"]').addClass('active');
                    $('.category-edit-form').removeClass('d--none');

                    $('.category-edit-form').find('.edit-category-lang').addClass('d-none');
                    $('.category-edit-form').find('.edit-category-lang[data-lang="en"]').removeClass('d-none');

                    updateBlogCategorySelectList();
                },
                error: function (response) {
                    if (response.status === 422) {
                        let errors = response.responseJSON.errors;
                        Object.keys(errors).forEach(function (key) {
                            toastr.error(errors[key][0]);
                        });
                    } else {
                        toastr.error(response.responseJSON.message || 'Something went wrong!');
                    }
                }
            });
        });

        $(document).on('click', '.delete-category-btn', function () {
            let categoryId = $(this).data('id');
            $('#confirmDelete').data('id', categoryId);
        });

        $(document).on("click", ".delete-category", function () {
            let getText = $("#get-confirm-and-cancel-button-text-for-blog-category-delete");
            let categoryId = $(this).data('id');
            let route = $(this).data('route');
            Swal.fire({
                title: getText.data("sure"),
                text: getText.data("text"),
                imageUrl: getText.data("image"),
                imageAlt: 'Custom image',
                showCancelButton: true,
                cancelButtonText: getText.data("cancel"),
                confirmButtonText: getText.data("confirm"),
                reverseButtons: true,
            }).then((result) => {
                if (result.value) {

                    $('.category-edit-form').addClass('d--none');
                    $('.category-create-form').removeClass('d--none');

                    $.ajax({
                        url: route,
                        method: 'DELETE',
                        data: {
                            _token: blogCSRFToken,
                            category_id: categoryId
                        },
                        success: function (response) {
                            $('#deleteModal').modal('hide');
                            toastr.success(response.message);
                            $('#categories-table').html(response.html);
                            let countCategory = $('.category-count');
                            countCategory.text(response.count);
                            updateBlogCategorySelectList();
                        },
                        error: function (xhr) {
                            $('#deleteModal').modal('hide');
                            toastr.error('Failed to delete the category.');
                        }
                    });
                }
            });
        });

        let searchTimeout = null;

        // Search form submit handler
        $('#search-form').on('submit', function (e) {
            e.preventDefault();
            clearTimeout(searchTimeout);
            fetchCategories(1); // Reset to first page when searching
        });

        // Handle pagination clicks
        $(document).on('click', '.page-link', function (e) {
            e.preventDefault();
            let page = $(this).attr('href')?.split('page=')[1] || $(this).data('page');
            if (page) {
                fetchCategories(parseInt(page));
            }
        });

        // Debounced search on typing
        // $('#datatableSearch').on('keyup', function () {
        //     clearTimeout(searchTimeout);
        //     searchTimeout = setTimeout(() => {
        //         fetchCategories(1); // Reset to first page when searching
        //     }, 500);
        // });

        // Fetch categories with search and pagination
        function fetchCategories(page) {
            let searchValue = $('#datatableSearch').val();

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': blogCSRFToken
                }
            });

            $.ajax({
                url: '{{ route("admin.blog.category.search") }}',
                method: 'POST',
                data: {
                    searchValue: searchValue,
                    page: page
                },
                beforeSend: function() {
                    $('#loading').show();
                },
                success: function(response) {
                    $('#categories-table').html(response.html);
                    $('.category-count').text(response.count);

                    // Update URL with search params without refreshing
                    let newUrl = window.location.pathname;
                    let params = new URLSearchParams(window.location.search);
                    if (searchValue) {
                        params.set('search', searchValue);
                    } else {
                        params.delete('search');
                    }
                    params.set('page', page);
                    newUrl += '?' + params.toString();
                    window.history.pushState({}, '', newUrl);
                },
                error: function(xhr, status, error) {
                    console.error('Search error details:', {
                        status: xhr.status,
                        responseText: xhr.responseText
                    });
                    toastr.error('{{ translate("Failed_to_fetch_categories") }}');
                },
                complete: function() {
                    $('#loading').hide();
                }
            });
        }

        // Status update handler
        $(document).on('change', '.toggle-switch-message', function() {
            let form = $(this).closest('form');
            $.ajax({
                url: form.attr('action'),
                method: 'GET',
                data: form.serialize(),
                success: function(response) {
                    toastr.success(response.message);
                    fetchCategories($('.page-item.active .page-link').text() || 1);
                }
            });
        });
    });

    function updateBlogCategorySelectList() {
        let blogCategorySelect = $('#blog-category-select');
        let message = blogCategorySelect.data("text");
        blogCategorySelect.empty().append(`<option value="null" selected disabled>---` + message +`---</option>`);

        $.get({
            url: blogCategorySelect.data('route'),
            dataType: "json",
            beforeSend: function () {
                //$("#loading").fadeIn();
            },
            success: function (response) {
                console.log(response)
                blogCategorySelect.empty().append(response?.select_tag);
            },
            complete: function () {
                //$("#loading").fadeOut();
            },
        });
    }
</script>
