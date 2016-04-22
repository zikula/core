// Copyright Zikula Foundation, licensed MIT.

( function($) {
    $(document).ready(function() {
        $('#togglebox').click( function() {
             $('#search_form input[type=checkbox]').prop('checked', $(this).prop('checked'));
        });
        $('#search_form input[type=checkbox]').click( function() {
            if ($(this).attr('id') !== 'togglebox') {
                $('#togglebox').prop('checked', false);
            }
        });
    });
})(jQuery);
