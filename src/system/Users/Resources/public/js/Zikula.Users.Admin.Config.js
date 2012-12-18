// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).

// Create the Zikula.Users object if needed
Zikula.define('Users.Admin');

// Create the Zikula.Users.Admin.Config object
Zikula.Users.Admin.Config = {
    /**
     * Initialize the script and form elements.
     */
    init: function()
    {
        Zikula.Users.Admin.Config.setup();
        
        if(typeof(document.theActiveElement) == 'undefined') {
            document.theActiveElement = null;
            var formElements = $(Zikula.Users.Admin.Config.formId).getElements();
            for (var i = 0, len = formElements.size(); i < len; i++) {
                formElements[i].observe('focus', function(){document.theActiveElement = this;});
            }
        }

        $(Zikula.Users.Admin.Config.registrationEnabledYesId).observe('click', Zikula.Users.Admin.Config.registrationEnabled_onChange);
        $(Zikula.Users.Admin.Config.registrationEnabledId).observe('click', Zikula.Users.Admin.Config.registrationEnabled_onChange);
        Zikula.Users.Admin.Config.registrationEnabled_onChange();

        $(Zikula.Users.Admin.Config.registrationModeratedYesId).observe('click', Zikula.Users.Admin.Config.registrationModerated_onClick);
        $(Zikula.Users.Admin.Config.registrationModeratedNoId).observe('click', Zikula.Users.Admin.Config.registrationModerated_onClick);
        $(Zikula.Users.Admin.Config.registrationVerificationModeUserPwdId).observe('click', Zikula.Users.Admin.Config.registrationVerificationMode_onClick);
        $(Zikula.Users.Admin.Config.registrationVerificationModeNoneId).observe('click', Zikula.Users.Admin.Config.registrationVerificationMode_onClick);
        Zikula.Users.Admin.Config.registrationModerationVerification_switchDisplayState();

        $(Zikula.Users.Admin.Config.registrationAntispamQuestionId).observe('blur', Zikula.Users.Admin.Config.registrationAntispamQuestion_onBlur);
        Zikula.Users.Admin.Config.registrationAntispamQuestion_onBlur();

        $(Zikula.Users.Admin.Config.loginMethodUserNameId).observe('click', Zikula.Users.Admin.Config.loginMethod_onClick);
        $(Zikula.Users.Admin.Config.loginMethodEmailId).observe('click', Zikula.Users.Admin.Config.loginMethod_onClick);
        $(Zikula.Users.Admin.Config.loginMethodEitherId).observe('click', Zikula.Users.Admin.Config.loginMethod_onClick);
        Zikula.Users.Admin.Config.loginMethod_onClick();
    },

    /**
     * Change event handler for the reg_allowreg field.
     */
    registrationEnabled_onChange: function()
    {
        Zikula.radioswitchdisplaystate(Zikula.Users.Admin.Config.registrationEnabledId, Zikula.Users.Admin.Config.registrationEnabledWrapId, false);
    },

    /**
     * Click event handler for the moderation field.
     */
    registrationModerated_onClick: function()
    {
        Zikula.Users.Admin.Config.registrationModerationVerification_switchDisplayState();
    },

    /**
     * Click event handler for the reg_verifyemail field.
     */
    registrationVerificationMode_onClick: function()
    {
        Zikula.Users.Admin.Config.registrationModerationVerification_switchDisplayState();
    },

    /**
     * Handles state changes for moderation and verification related fields.
     */
    registrationModerationVerification_switchDisplayState: function()
    {
        var moderationObjGroup = $(Zikula.Users.Admin.Config.registrationModeratedId);
        var verificationObjGroup = $(Zikula.Users.Admin.Config.registrationVerificationModeId);
        var approvalOrderWrap = $(Zikula.Users.Admin.Config.registrationApprovalOrderWrapId);
        var autoLoginWrap = $(Zikula.Users.Admin.Config.registrationAutoLoginWrapId);

        var moderation_state = moderationObjGroup.select('input[type=radio][value="1"]').pluck('checked').any();
        var verification_state = verificationObjGroup.select('input[type=radio][value="0"]').pluck('checked').any();
        var approvalOrder_state = (moderation_state && !verification_state);
        var autoLogin_state = (!moderation_state && verification_state);

        if (approvalOrder_state == true) {
            if (approvalOrderWrap.getStyle('display') == 'none') {
                if (typeof(Effect) != 'undefined') {
                    Effect.BlindDown(approvalOrderWrap);
                } else {
                    approvalOrderWrap.show();
                }
            }
        } else {
            if (approvalOrderWrap.getStyle('display') != 'none') {
                if (typeof(Effect) != 'undefined') {
                    Effect.BlindUp(approvalOrderWrap);
                } else {
                    approvalOrderWrap.hide();
                }
            }
        }

        if (autoLogin_state == true) {
            if (autoLoginWrap.getStyle('display') == 'none') {
                if (typeof(Effect) != 'undefined') {
                    Effect.BlindDown(autoLoginWrap);
                } else {
                    autoLoginWrap.show();
                }
            }
        } else {
            if (autoLoginWrap.getStyle('display') != 'none') {
                if (typeof(Effect) != 'undefined') {
                    Effect.BlindUp(autoLoginWrap);
                } else {
                    autoLoginWrap.hide();
                }
            }
        }
    },

    /**
     * Blur event handler for the reg_question field.
     */
    registrationAntispamQuestion_onBlur: function()
    {
        var regAnswerMandatory = $(Zikula.Users.Admin.Config.registrationAntispamAnswerMandatoryId);

        if ($F(Zikula.Users.Admin.Config.registrationAntispamQuestionId).blank()) {
            if (!regAnswerMandatory.hasClassName('z-hide')) {
                regAnswerMandatory.addClassName('z-hide');
            }
        } else {
            if (regAnswerMandatory.hasClassName('z-hide')) {
                regAnswerMandatory.removeClassName('z-hide');
            }
        }
    },

    /**
     * Click event handler for the loginviaoption field.
     */
    loginMethod_onClick: function()
    {
        var loginViaOptionGroup = $(Zikula.Users.Admin.Config.loginMethodId);
        var emailOption = loginViaOptionGroup.select('input[type=radio][value="1"]').pluck('checked').any();
        var eitherOption = loginViaOptionGroup.select('input[type=radio][value="2"]').pluck('checked').any();

        Zikula.Users.Admin.Config.requireUniqueEmail_save = $(Zikula.Users.Admin.Config.requireUniqueEmailYesId).checked;
        if (emailOption || eitherOption) {
            $(Zikula.Users.Admin.Config.requireUniqueEmailYesId).checked = true;
            $(Zikula.Users.Admin.Config.requireUniqueEmailNoId).disabled = true;
        } else {
            $(Zikula.Users.Admin.Config.requireUniqueEmailYesId).checked = Zikula.Users.Admin.Config.requireUniqueEmail_save;
            $(Zikula.Users.Admin.Config.requireUniqueEmailNoId).checked = !Zikula.Users.Admin.Config.requireUniqueEmail_save;
            $(Zikula.Users.Admin.Config.requireUniqueEmailNoId).disabled = false;
        }
    }
}

// Load and execute the initialization when the DOM is ready. This must be below the definition of the init function!
document.observe('dom:loaded', Zikula.Users.Admin.Config.init);
