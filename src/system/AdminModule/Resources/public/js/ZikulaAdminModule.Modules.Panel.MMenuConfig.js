// Copyright Zikula, licensed MIT.

(function($) {
    $(document).ready(function() {
        $('#zikula-admin-hiddenpanel-menu').mmenu({
            extensions: ['hiddenpanel-customwidth'],
            'header': {
                'title': Translator.trans('Administration'),
                'add': true,
                'update': true
            },
            'searchfield': true
        });
        $('#zikula-admin-hiddenpanel-menu').removeClass('d-none');
    });
})(jQuery);
