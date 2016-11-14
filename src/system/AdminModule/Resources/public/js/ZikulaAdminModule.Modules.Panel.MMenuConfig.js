// Copyright Zikula Foundation, licensed MIT.

( function($) {
    $(document).ready(function() {
        $('#zikula-admin-hiddenpanel-menu').mmenu({
            extensions: ['hiddenpanel-customwidth'],
            'header': {
                'title': 'Administration',
                'add': true,
                'update': true
            },
            'searchfield': true
        });
    });
})(jQuery);
