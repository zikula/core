// Copyright Zikula Foundation, licensed MIT.

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
