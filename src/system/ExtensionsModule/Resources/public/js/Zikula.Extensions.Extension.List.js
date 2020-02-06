// Copyright Zikula Foundation, licensed MIT.

(function ($) {
    $(document).ready(function () {
        $('i[class*=fa-caret-]').on('click', function() {
            $(this).toggleClass('fa-caret-down fa-caret-up');
        });
        $('[data-toggle="tooltip"]').tooltip();

        $('.filter-button').click(function () {
            if (!$(this).hasClass('active')) {
                $('.filter-button.active').not(this).removeClass('active');

                var selectors = $(this).data('selectors').split(' ').join(',');
                $('#extension-list tr').addClass('d-none');
                $(selectors).removeClass('d-none');
            } else {
                $('#extension-list tr').removeClass('d-none');
            }
        });
    });
})(jQuery);
