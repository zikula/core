/**
 * Copyright Zikula Foundation 2014 - license GNU/LGPLv3 (or at your option, any later version).
 */
(function($) {
    $(document).ready(function() {
        /**
         * Force "User Name" and "Email Address" to lowercase.
         */
        $('.to-lower-case').blur(function() {
            $(this).val($(this).val().toLowerCase());
        });
    });
})(jQuery);
