// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).

// Create the Zikula.Users object if needed
Zikula.define('Users');

// Create the Zikula.Users.ModifyConfig object
Zikula.Users.ModifyConfig = {
    /**
     * Initialize the script and form elements.
     */
    init: function()
    {
        if(typeof(document.theActiveElement) == 'undefined') {
            document.theActiveElement = null;
            var formElements = $('users_modifyconfig_form').getElements();
            for (var i = 0, len = formElements.size(); i < len; i++) {
                formElements[i].observe('focus', function(){document.theActiveElement = this;});
            }
        }

        $('users_reg_allowregyes').observe('click', Zikula.Users.ModifyConfig.users_reg_allowreg_onChange);
        $('users_reg_allowregno').observe('click', Zikula.Users.ModifyConfig.users_reg_allowreg_onChange);
        Zikula.Users.ModifyConfig.users_reg_allowreg_onChange();

        $('users_moderationyes').observe('click', Zikula.Users.ModifyConfig.users_moderation_onClick);
        $('users_moderationno').observe('click', Zikula.Users.ModifyConfig.users_moderation_onClick);
        $('users_reg_verifyemail2').observe('click', Zikula.Users.ModifyConfig.users_verification_onClick);
        $('users_reg_verifyemail0').observe('click', Zikula.Users.ModifyConfig.users_verification_onClick);
        Zikula.Users.ModifyConfig.users_moderation_order_switchDisplayState();

        $('users_reg_question').observe('blur', Zikula.Users.ModifyConfig.users_reg_question_onBlur);
        Zikula.Users.ModifyConfig.users_reg_question_onBlur();

        $('users_loginviausername').observe('click', Zikula.Users.ModifyConfig.users_loginviaoption_onClick);
        $('users_loginviaemail').observe('click', Zikula.Users.ModifyConfig.users_loginviaoption_onClick);
        Zikula.Users.ModifyConfig.users_loginviaoption_onClick();
    },

    /**
     * Change event handler for the reg_allowreg field.
     */
    users_reg_allowreg_onChange: function()
    {
        Zikula.radioswitchdisplaystate('users_reg_allowreg', 'users_reg_allowreg_wrap', false);
    },

    /**
     * Click event handler for the moderation field.
     */
    users_moderation_onClick: function()
    {
        Zikula.Users.ModifyConfig.users_moderation_order_switchDisplayState();
    },

    /**
     * Click event handler for the reg_verifyemail field.
     */
    users_verification_onClick: function()
    {
        Zikula.Users.ModifyConfig.users_moderation_order_switchDisplayState();
    },

    /**
     * Handles state changes for moderation and verification related fields.
     */
    users_moderation_order_switchDisplayState: function()
    {
        var moderationObjGroup = $('users_moderation');
        var verificationObjGroup = $('users_reg_verifyemail');
        var objCont = $('users_moderation_order_wrap');

        check_state = moderationObjGroup.select('input[type=radio][value="1"]').pluck('checked').any();
        check_state = (check_state && !verificationObjGroup.select('input[type=radio][value="0"]').pluck('checked').any());

        if (check_state == true) {
            if (objCont.getStyle('display') == 'none') {
                if (typeof(Effect) != 'undefined') {
                    Effect.BlindDown(objCont);
                } else {
                    objCont.show();
                }
            }
        } else {
            if (objCont.getStyle('display') != 'none') {
                if (typeof(Effect) != 'undefined') {
                    Effect.BlindUp(objCont);
                } else {
                    objCont.hide();
                }
            }
        }
    },

    /**
     * Blur event handler for the reg_question field.
     */
    users_reg_question_onBlur: function()
    {
        var regAnswerMandatory = $('users_reg_answer_mandatory');

        if ($F('users_reg_question').blank()) {
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
    users_loginviaoption_onClick: function()
    {
        var loginViaOptionGroup = $('users_loginviaoption');
        var emailOption = loginViaOptionGroup.select('input[type=radio][value="1"]').pluck('checked').any();

        Zikula.Users.ModifyConfig.save_reg_uniemail = $('reg_uniemailyes').checked;
        if (emailOption) {
            $('reg_uniemailyes').checked = true;
            $('reg_uniemailno').disabled = true;
        } else {
            $('reg_uniemailyes').checked = Zikula.Users.ModifyConfig.save_reg_uniemail;
            $('reg_uniemailno').checked = !Zikula.Users.ModifyConfig.save_reg_uniemail;
            $('reg_uniemailno').disabled = false;
        }
    }
}

// Load and execute the initialization when the DOM is ready. This must be below the definition of the init function!
document.observe('dom:loaded', Zikula.Users.ModifyConfig.init);
