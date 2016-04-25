// Copyright Zikula Foundation, licensed MIT.

// @deprecated at Core-2.0 - do not convert to twig

( function($) {
    $(document).ready(function() {
        $('#newtheme').change( function() {
            var selectedTheme;

            selectedTheme = $(this).find(':selected');
            $('#preview').attr({
                src: selectedTheme.data('previewimage'),
                title: selectedTheme.attr('title'),
                alt: selectedTheme.attr('title')
            });
        });
    });
})(jQuery);
