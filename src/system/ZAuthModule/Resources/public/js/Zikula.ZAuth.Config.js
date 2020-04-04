// Copyright Zikula Foundation, licensed MIT.

(function($) {
    var antiSpamQuestionElementId = '';
    var antiSpamAnswerElementId = '';

    /**
     * Show & require or hide anti-spam answer based on question value.
     */
    function toggleAntiSpamAnswerDisplay() {
        if ($('#' + antiSpamQuestionElementId).val() != '') {
            $('label[for="' + antiSpamAnswerElementId + '"]').addClass('required');
            $('#' + antiSpamAnswerElementId).prop('required', true);
            $('#antispam_answer').collapse('show');
        } else {
            $('label[for="' + antiSpamAnswerElementId + '"]').removeClass('required');
            $('#' + antiSpamAnswerElementId).prop('required', false).val('');
            $('#antispam_answer').collapse('hide');
        }
    }

    $(document).ready(function() {
        antiSpamQuestionElementId = $('#antispamQuestionIdentifiers').data('question');
        antiSpamAnswerElementId = $('#antispamQuestionIdentifiers').data('answer');

        // set up event handlers
        $('#' + antiSpamQuestionElementId).on('change', toggleAntiSpamAnswerDisplay);

        // initialize form - show|hide appropriate inputs
        toggleAntiSpamAnswerDisplay();
    });

})(jQuery);
