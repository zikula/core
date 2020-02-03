// Copyright Zikula Foundation, licensed MIT.

(function ($) {
    $(document).ready(function () {
        $('i[class*=fa-caret-]').on('click', function() {
            $(this).toggleClass('fa-caret-down fa-caret-up');
        });
        $('[data-toggle="tooltip"]').tooltip();

        $('.filter-button').click(function () {
            var $button = $(this);
            var $selectors = $button.data('selectors').split(' ').join(',');
            $($selectors).toggleClass('d-none');
            var textToggle = $button.data('text-toggle');
            var currentText = $button.find('span').text();
            $button.find('span').text(textToggle);
            $button.data('text-toggle', currentText);
        });
    });
})(jQuery);
