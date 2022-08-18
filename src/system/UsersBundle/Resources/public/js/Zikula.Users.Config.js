// Copyright Zikula, licensed MIT.

(function($) {
    var identifiersElement = '';

    /**
     * Show|hide moderation-related form elements based on selections within the form.
     */
    function toggleModerationItemsDisplay() {
        var autoLoginWrap = $('#' + identifiersElement.data('auto-login') + '_wrap');
        var isModerated = $('#' + identifiersElement.data('approval-required')).is(':checked');
        if (true === isModerated) {
            autoLoginWrap.collapse('hide');
        } else {
            autoLoginWrap.collapse('show');
        }
    }

    /**
     * Show|hide the registration disabled notice based on enabled value.
     */
    function toggleRegistrationDisabledDisplay() {
        if ($('#' + identifiersElement.data('registration-enabled')).is(':checked')) {
            $('#registration_disabled_reason').collapse('hide');
        } else {
            $('#registration_disabled_reason').collapse('show');
        }
    }

    $(document).ready(function() {
        identifiersElement = $('#configElementIdentifiers');

        // set up event handlers
        $('#' + identifiersElement.data('registration-enabled')).on('click', toggleRegistrationDisabledDisplay);
        $('.registration-moderation-input').on('click', toggleModerationItemsDisplay);

        // initialize form - show|hide appropriate inputs
        toggleRegistrationDisabledDisplay();
        toggleModerationItemsDisplay();
    });
})(jQuery);
