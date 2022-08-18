// Copyright Zikula, licensed MIT.

(function($) {
    function updateDynamicFields() {
        $('#uploadSettings').toggleClass('d-none', !$('#zikulaprofilemodule_config_allowUploads').prop('checked'));
        $('#shrinkSettings').toggleClass('d-none', !$('#zikulaprofilemodule_config_shrinkLargeImages').prop('checked'));
    }

    $(document).ready(function() {
        $('#zikulaprofilemodule_config_allowUploads, #zikulaprofilemodule_config_shrinkLargeImages').change(updateDynamicFields);
        updateDynamicFields();
    });
})(jQuery);
