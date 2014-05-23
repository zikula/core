jQuery( document ).ready(function() {
    jQuery('#newtheme').change( function() {
        var previewSrc = jQuery(this).find(':selected').data('previewimage');
        jQuery('#preview').attr('src', previewSrc);
    });
});