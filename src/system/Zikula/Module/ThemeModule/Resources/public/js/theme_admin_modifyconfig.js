// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).

(function($) {
    $(function() { 
        if ($('#alt_theme_name').val() == "") {
            // Not set
            $('#alt_theme_domain').parent().parent().hide();
        }

        $("#alt_theme_name").click(function() {
            if ($('#alt_theme_name').val() == "") {
                $('#alt_theme_domain').parent().parent().fadeOut();
            } else {
                $('#alt_theme_domain').parent().parent().fadeIn();
            }
        });
    });
})(jQuery);
