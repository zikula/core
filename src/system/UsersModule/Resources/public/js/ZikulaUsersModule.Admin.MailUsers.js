// Copyright Zikula Foundation, licensed MIT.

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
