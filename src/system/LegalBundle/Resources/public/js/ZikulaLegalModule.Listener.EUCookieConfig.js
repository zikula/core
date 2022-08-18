// Copyright Zikula, licensed MIT.

( function($) {
    $(document).ready(function() {
        $.cookieBar({
            message: Translator.trans('We use cookies to track usage and preferences.'),
            acceptText: Translator.trans('I Understand'),
            element: '.navbar.fixed-top',
            append: true
        });
    });
})(jQuery);
