// Copyright Zikula, licensed MIT.

(function ($) {
    $(document).ready(function () {
        $("a[href^='http']").attr('target', '_blank');
    });
})(jQuery);
