// Copyright Zikula Foundation, licensed MIT.

(function ($) {
    $(document).ready(function () {
        $('i[class*=fa-caret-]').on('click', function() {
            $(this).toggleClass('fa-caret-down fa-caret-up');
        });
        $('[data-toggle="tooltip"]').tooltip();

        $('.filter-button').addClass('active').click(function () {
            var $button = $(this);
            var textToggle = $button.data('text-toggle');
            var currentText = $button.find('span').text();
            $button.find('span').text(textToggle);
            $button.data('text-toggle', currentText);

            $(this).toggleClass('active');
            var $selectors = [];
            $('.filter-button.active').each(function () {
                $selectors.push($(this).data('selectors').split(' ').join(','));
            });
            $(this).toggleClass('active');

            $('#extension-list tr').removeClass('d-none');
            if ($selectors.length > 0) {
                $('#extension-list tr').addClass('d-none');
                $($selectors.join(',')).removeClass('d-none');
            }
        });
    });
})(jQuery);
