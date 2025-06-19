$(window).on("load", function () {
    $("#app-blog-preloader").fadeOut("slow", function () {
        $(this).remove();
    });
});

// ---- blog top menu scrollable start
$(document).ready(function () {

    document.querySelectorAll('.scrollspy-blog-details table').forEach(function(table) {
        if (!table.parentElement.classList.contains('table-responsive')) {
        const wrapper = document.createElement('div');
        wrapper.classList.add('table-responsive');
        table.parentNode.insertBefore(wrapper, table);
        wrapper.appendChild(table);
        }
    });

    $('.article-nav-wrapper_collapse').on('click', function () {
        const wrapper = $('.article-nav-wrapper');
        const openIcon = $('.open-icon');
        const closeIcon = $('.close-icon');

        if (wrapper.hasClass('d-none')) {
            wrapper.removeClass('d-none').hide().slideDown(300);
            openIcon.addClass('d-none');
            closeIcon.removeClass('d-none');
        } else {
            wrapper.slideUp(300, function () {
                $(this).addClass('d-none');
            });
            closeIcon.addClass('d-none');
            openIcon.removeClass('d-none');
        }
    });
    const $blogMainContainer = $('.blog-root-container');

    $('.scrollspy-blog-details-menu li a').on('click', function (e) {
        e.preventDefault();
        $('.scrollspy-blog-details-menu li').removeClass('active');
        $(this).parent('li').addClass('active');
        const target = $(this).attr('href');

        if ($blogMainContainer?.data('platform')?.toString() === 'app') {
            const container = document.querySelector('.blog-root-container');
            const targetElementId = document.querySelector(target);

            const targetPosition = targetElementId.getBoundingClientRect().top + container.scrollTop + 5;

            container.scrollTo({
                top: targetPosition - 5,
                behavior: 'smooth'
            });

        } else {
            $('html, body').animate({
                scrollTop: $(target).offset().top - 80
            }, 300);
        }
    });

    $('.blog-root-container').on('scroll', function () {
        let scrollTimeout;
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(function () {
            if ($blogMainContainer?.data('platform')?.toString() === 'app') {
                const scrollPosition = document.querySelector('.blog-root-container').scrollTop;
                $('.scrollspy-blog-details-menu li a').each(function () {
                    const target = $(this).attr('href');
                    if (target && target.startsWith('#')) {
                        const section = document.querySelector(target);

                        if (section) {
                            const sectionTop = section.getBoundingClientRect().top + scrollPosition - 100;
                            const sectionBottom = sectionTop + section.offsetHeight;

                            if (scrollPosition >= sectionTop && scrollPosition < sectionBottom) {
                                $('.scrollspy-blog-details-menu li').removeClass('active');
                                $(this).parent('li').addClass('active');
                            }
                        }
                    }
                });
            }
        }, 50);
    });

    $(window).on('scroll', function () {
        if ($blogMainContainer?.data('platform')?.toString() === 'web') {
            const scrollPosition = $(window).scrollTop();
            $('.scrollspy-blog-details-menu li a').each(function () {
                const target = $(this).attr('href');
                const section = $(target);
                if (section.length) {
                    const sectionTop = section.offset().top - 100;
                    const sectionBottom = sectionTop + section.outerHeight();
                    if (scrollPosition >= sectionTop && scrollPosition < sectionBottom) {
                        $('.scrollspy-blog-details-menu li').removeClass('active');
                        $(this).parent('li').addClass('active');
                    }
                }
            });
        }
    });

    function checkNavOverflow() {
        try {
            let $nav = $(".blog-top-nav");
            let $btnNext = $(".blog-top-nav_next-btn");
            let $btnPrev = $(".blog-top-nav_prev-btn");
            let isRTL = $("html").attr("dir") === "rtl";
            let navScrollWidth = $nav[0].scrollWidth;
            let navClientWidth = $nav[0].clientWidth;
            let scrollLeft = $nav.scrollLeft();

            if (isRTL) {
                $btnNext.find("i").removeClass('czi-arrow-right').addClass('czi-arrow-left');
                $btnPrev.find("i").removeClass('czi-arrow-left').addClass('czi-arrow-right');

                let scrollRight = navScrollWidth - navClientWidth + scrollLeft;

                $btnNext.css("display", scrollRight > 1 ? "flex" : "none");
                $btnPrev.css("display", scrollLeft < -1 ? "flex" : "none");
            } else {
                $btnNext.find("i").removeClass('czi-arrow-left').addClass('czi-arrow-right');
                $btnPrev.find("i").removeClass('czi-arrow-right').addClass('czi-arrow-left');

                $btnNext.css("display", navScrollWidth > navClientWidth && scrollLeft + navClientWidth < navScrollWidth ? "flex" : "none");
                $btnPrev.css("display", scrollLeft > 1 ? "flex" : "none");
            }
        } catch (error) {

        }
    }

    checkNavOverflow();

    $(window).on("resize", function () {
        checkNavOverflow();
    });

    $(".blog-top-nav").on("scroll", function () {
        checkNavOverflow();
    });

    $(document).on('click', '.blog-top-nav_next-btn', function () {
        let $nav = $(".blog-top-nav");
        let scrollWidth = $nav.find("li").outerWidth(true);
        let isRTL = $("html").attr("dir") === "rtl";

        if (isRTL) {
            $nav.animate(
                {scrollLeft: $nav.scrollLeft() - scrollWidth},
                300,
                checkNavOverflow
            );
        } else {
            $nav.animate(
                {scrollLeft: $nav.scrollLeft() + scrollWidth},
                300,
                checkNavOverflow
            );
        }
    });

    $(document).on('click', '.blog-top-nav_prev-btn', function () {
        let $nav = $(".blog-top-nav");
        let scrollWidth = $nav.find("li").outerWidth(true);
        let isRTL = $("html").attr("dir") === "rtl";

        if (isRTL) {
            $nav.animate(
                {scrollLeft: $nav.scrollLeft() + scrollWidth},
                300,
                checkNavOverflow
            );
        } else {
            $nav.animate(
                {scrollLeft: $nav.scrollLeft() - scrollWidth},
                300,
                checkNavOverflow
            );
        }
    });
});

$(document).on('click', '.clear-all-search', function () {
    $('#search').val('');
    $('#search-form').submit();
});

$(document).on('click', '.clear-all-search-popular', function () {
    $('#popular-search').val('');
    $('#popular-search-form').submit();
});

$('.blog-single-card-item').on('click', function (e) {
    if ($(e.target).is('a') || $(e.target).closest('a').length) {
        return;
    }
    window.location.href = $(this).data('route');
});
