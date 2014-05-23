jQuery( document ).ready(function() {
    jQuery('#newtheme').change( function() {
        var $selectedTheme = jQuery(this).find(':selected')
        jQuery('#preview').attr('src', $selectedTheme.data('previewimage'));
        jQuery('#preview').attr('title', $selectedTheme.attr('title'));
        jQuery('#preview').attr('alt', $selectedTheme.attr('title'));
    });
});