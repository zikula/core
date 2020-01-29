// Copyright Zikula Foundation, licensed MIT.

(function($) {
    $(document).ready(function() {
        $('#togglebox').click( function() {
             $('.search input[type=checkbox]').prop('checked', $(this).prop('checked'));
        });
        $('.search input[type=checkbox]').click(function () {
            if ('togglebox' !== $(this).attr('id')) {
                $('#togglebox').prop('checked', false);
            }
        });
    });
})(jQuery);
