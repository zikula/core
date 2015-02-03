// Copyright Zikula Foundation 2014 - license GNU/LGPLv3 (or at your option, any later version).

( function($) {
    $(document).ready(function() {
        $('#select-all').click( function(event) {
            event.preventDefault();
            $('.user-checkboxes').prop('checked', true);
        });
        $('#deselect-all').click( function(event) {
            event.preventDefault();
            $('.user-checkboxes').prop('checked', false);
        });
    });
})(jQuery);
