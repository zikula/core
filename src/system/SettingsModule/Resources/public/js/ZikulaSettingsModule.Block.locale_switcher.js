// Copyright Zikula Foundation, licensed MIT.

( function($) {
    $(document).ready(function() {
        $(function() {
            $('.locale-switcher-block').on('change', function () {
                window.location = $(this).val();
            })
        });
    });
})(jQuery);

