// Copyright Zikula Foundation, licensed MIT.

( function($) {
    $(document).ready(function() {
        $('#toggle_notallowed').click( function() {
             $('.notallowed_radio').prop('checked', true);
        });
        $('#toggle_allowed').click( function() {
             $('.allowed_radio').prop('checked', true);
        });
        $('#toggle_allowedwith').click( function() {
             $('.allowedwith_radio').prop('checked', true);
        });
        
        $('.notallowed_radio, .allowed_radio, .allowedwith_radio').change( function() {
            $('#toggle_notallowed, #toggle_allowed, #toggle_allowedwith').prop('checked', true);
        });
    });
})(jQuery);
