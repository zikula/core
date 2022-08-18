// Copyright Zikula, licensed MIT.

(function($) {
    function updateDynamicFields() {
        $('#uploadSettings').toggleClass('d-none', !$('#zikulaprofilebundle_config_allowUploads').prop('checked'));
        $('#shrinkSettings').toggleClass('d-none', !$('#zikulaprofilebundle_config_shrinkLargeImages').prop('checked'));
    }

    $(document).ready(function() {
        $('#zikulaprofilebundle_config_allowUploads, #zikulaprofilebundle_config_shrinkLargeImages').change(updateDynamicFields);
        updateDynamicFields();
    });
})(jQuery);
