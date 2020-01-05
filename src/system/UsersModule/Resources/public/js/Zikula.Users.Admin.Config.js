// Copyright Zikula Foundation, licensed MIT.

var ZikulaUsersAdminConfig = {};

(function($) {

    /**
     * Initialize the script and form elements.
     */
    ZikulaUsersAdminConfig.init = function() {
        ZikulaUsersAdminConfig.setup();

        $('#' + ZikulaUsersAdminConfig.registrationModeratedYesId).click(ZikulaUsersAdminConfig.registrationModerationVerification_switchDisplayState);
        $('#' + ZikulaUsersAdminConfig.registrationModeratedNoId).click(ZikulaUsersAdminConfig.registrationModerationVerification_switchDisplayState);
        $('#' + ZikulaUsersAdminConfig.registrationVerificationModeUserPwdId).click(ZikulaUsersAdminConfig.registrationModerationVerification_switchDisplayState);
        $('#' + ZikulaUsersAdminConfig.registrationVerificationModeNoneId).click(ZikulaUsersAdminConfig.registrationModerationVerification_switchDisplayState);
        ZikulaUsersAdminConfig.registrationModerationVerification_switchDisplayState();

        $('#' + ZikulaUsersAdminConfig.registrationAntispamQuestionId).blur(ZikulaUsersAdminConfig.registrationAntispamQuestion_onBlur);
        ZikulaUsersAdminConfig.registrationAntispamQuestion_onBlur();

        $('#' + ZikulaUsersAdminConfig.loginMethodUserNameId).click(ZikulaUsersAdminConfig.loginMethod_onClick);
        $('#' + ZikulaUsersAdminConfig.loginMethodEmailId).click(ZikulaUsersAdminConfig.loginMethod_onClick);
        $('#' + ZikulaUsersAdminConfig.loginMethodEitherId).click(ZikulaUsersAdminConfig.loginMethod_onClick);
        ZikulaUsersAdminConfig.loginMethod_onClick();
    };

    /**
     * Handles state changes for moderation and verification related fields.
     */
    ZikulaUsersAdminConfig.registrationModerationVerification_switchDisplayState = function() {
        var moderationObjGroup = $('#' + ZikulaUsersAdminConfig.registrationModeratedId);
        var verificationObjGroup = $('#' + ZikulaUsersAdminConfig.registrationVerificationModeId);
        var approvalOrderWrap = $('#' + ZikulaUsersAdminConfig.registrationApprovalOrderWrapId);
        var autoLoginWrap = $('#' + ZikulaUsersAdminConfig.registrationAutoLoginWrapId);

        var isModerated = moderationObjGroup.find('input[type=radio][value="1"]:checked').length > 0;
        var isVerified = verificationObjGroup.find('input[type=radio][value="0"]:checked').length > 0;

        var approvalOrder_state = (isModerated && !isVerified);
        var autoLogin_state = (!isModerated && isVerified);

        approvalOrderWrap.toggleClass('d-none', true !== approvalOrder_state);
        autoLoginWrap.toggleClass('d-none', true !== autoLogin_state);
    };

    /**
     * Blur event handler for the reg_question field.
     */
    ZikulaUsersAdminConfig.registrationAntispamQuestion_onBlur = function() {
        var regQuestion = $('#' + ZikulaUsersAdminConfig.registrationAntispamQuestionId);
        var regAnswerMandatory = $('#' + ZikulaUsersAdminConfig.registrationAntispamAnswerMandatoryId);

        regAnswerMandatory.toggleClass('d-none', '' === $.trim(regQuestion.val()));
    };

    /**
     * Click event handler for the loginviaoption field.
     */
    ZikulaUsersAdminConfig.loginMethod_onClick = function() {
        var loginViaOptionGroup = $('#' + ZikulaUsersAdminConfig.loginMethodId);
        var emailOption = loginViaOptionGroup.find('input[type=radio][value="1"]:checked').length > 0;
        var eitherOption = loginViaOptionGroup.find('input[type=radio][value="2"]:checked').length > 0;

        ZikulaUsersAdminConfig.requireUniqueEmail_save = $('#' + ZikulaUsersAdminConfig.requireUniqueEmailYesId).prop('checked');
        if (emailOption || eitherOption) {
            $('#' + ZikulaUsersAdminConfig.requireUniqueEmailYesId).prop('checked', true);
            $('#' + ZikulaUsersAdminConfig.requireUniqueEmailNoId).prop('disabled', true);
        } else {
            $('#' + ZikulaUsersAdminConfig.requireUniqueEmailYesId).prop('checked', ZikulaUsersAdminConfig.requireUniqueEmail_save);
            $('#' + ZikulaUsersAdminConfig.requireUniqueEmailNoId).prop('checked', !ZikulaUsersAdminConfig.requireUniqueEmail_save);
            $('#' + ZikulaUsersAdminConfig.requireUniqueEmailNoId).prop('disabled', false);
        }
    };

    $(document).ready(function() {
        ZikulaUsersAdminConfig.init();
    });
})(jQuery);
