// Copyright Zikula, licensed MIT.

(function ($) {
    $(document).ready(function () {
        if ($('#hardResetParameters').length < 1) {
            return;
        }
        $('#' + $('#hardResetParameters').data('elementid')).click(function (event) {
            if (!confirm(Translator.trans('Warning! Do you really want to reset ALL displayname, url and description to defaults? This may break your existing indexed URLs, affecting SEO.'))) {
                $(this).prop('checked', false);
            }
        });
    });
})(jQuery);
