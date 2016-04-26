// Copyright Zikula Foundation, licensed MIT.

// @deprecated at Core-2.0 - do not convert to twig

( function($) {
    $(document).ready(function() {
        if ($('#alt_theme_name').val() == '') {
            // Not set
            $('#alt_theme_domain').parent().parent().hide();
        }

        $('#alt_theme_name').change(function() {
            if ($('#alt_theme_name').val() == '') {
                $('#alt_theme_domain').parent().parent().fadeOut();
            } else {
                $('#alt_theme_domain').parent().parent().fadeIn();
            }
        });
    });
})(jQuery);
