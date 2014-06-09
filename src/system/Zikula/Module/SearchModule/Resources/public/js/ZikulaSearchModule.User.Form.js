// Copyright Zikula Foundation 2013 - license GNU/LGPLv3 (or at your option, any later version).

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