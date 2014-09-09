// Copyright Zikula Foundation 2014 - license GNU/LGPLv3 (or at your option, any later version).

( function($) {
    $(document).ready(function() {
        $('#newtheme').change( function() {
            var selectedTheme;

            selectedTheme = $(this).find(':selected')
            $('#preview').attr({
                src: selectedTheme.data('previewimage'),
                title: selectedTheme.attr('title'),
                alt: selectedTheme.attr('title')
            });
        });
    });
})(jQuery);
