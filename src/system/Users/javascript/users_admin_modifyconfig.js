// Copyright Zikula Foundation 2009 - license GNU/LGPLv2.1 (or at your option, any later version).

if (typeof(UsersModifyConfig) == 'undefined') {
    UsersModifyConfig = {};
}

UsersModifyConfig.init = function()
{
    if(typeof(document.theActiveElement) == 'undefined') {
        document.theActiveElement = null;
        var formElements = $('users_modifyconfig_form').getElements();
        for (var i = 0, len = formElements.size(); i < len; i++) {
            formElements[i].observe('focus', function(){document.theActiveElement = this;});
        }
    }

    Event.observe('users_reg_allowregyes', 'click', UsersModifyConfig.users_reg_allowreg_onchange);
    Event.observe('users_reg_allowregno', 'click', UsersModifyConfig.users_reg_allowreg_onchange);
    UsersModifyConfig.users_reg_allowreg_onchange();
    //radioswitchdisplaystate('users_reg_allowreg', 'users_reg_allowreg_wrap', false);

    Event.observe('users_moderationyes', 'click', UsersModifyConfig.users_moderation_onclick);
    Event.observe('users_moderationno', 'click', UsersModifyConfig.users_moderation_onclick);
    Event.observe('users_reg_verifyemail2', 'click', UsersModifyConfig.users_verification_onclick);
    Event.observe('users_reg_verifyemail0', 'click', UsersModifyConfig.users_verification_onclick);
    UsersModifyConfig.users_moderation_order_switchDisplayState();

    Event.observe('users_reg_question', 'blur', UsersModifyConfig.users_reg_question_onblur);
    UsersModifyConfig.users_reg_question_onblur();

    Event.observe('users_loginviausername', 'click', UsersModifyConfig.users_loginviaoption_onclick);
    Event.observe('users_loginviaemail', 'click', UsersModifyConfig.users_loginviaoption_onclick);
    UsersModifyConfig.users_loginviaoption_onclick();
}

UsersModifyConfig.users_reg_allowreg_onchange = function()
{
    Zikula.radioswitchdisplaystate('users_reg_allowreg', 'users_reg_allowreg_wrap', false);
}

UsersModifyConfig.users_moderation_onclick = function()
{
    UsersModifyConfig.users_moderation_order_switchDisplayState();
}

UsersModifyConfig.users_verification_onclick = function()
{
    UsersModifyConfig.users_moderation_order_switchDisplayState();
}

UsersModifyConfig.users_moderation_order_switchDisplayState = function()
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
}

UsersModifyConfig.users_reg_question_onblur = function()
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
}

UsersModifyConfig.users_loginviaoption_onclick = function()
{
    var loginViaOptionGroup = $('users_loginviaoption');
    var emailOption = loginViaOptionGroup.select('input[type=radio][value="1"]').pluck('checked').any();

    if (emailOption) {
        UsersModifyConfig.save_reg_uniemail = $('reg_uniemailyes').checked;
        $('reg_uniemailyes').checked = true;
        $('reg_uniemailno').disabled = true;
    } else {
        $('reg_uniemailyes').checked = UsersModifyConfig.save_reg_uniemail;
        $('reg_uniemailno').checked = !UsersModifyConfig.save_reg_uniemail;
        $('reg_uniemailno').disabled = false;
    }
}

document.observe('dom:loaded', UsersModifyConfig.init);
