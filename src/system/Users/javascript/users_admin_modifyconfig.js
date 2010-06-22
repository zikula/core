// Copyright Zikula Foundation 2009 - license GNU/LGPLv2.1 (or at your option, any later version).

Event.observe(window, 'load', users_modifyconfig_init, false);

function users_modifyconfig_init()
{
    Event.observe('reg_allowregyes', 'click', users_reg_allowreg_onchange, false);
    Event.observe('reg_allowregno', 'click', users_reg_allowreg_onchange, false);
    radioswitchdisplaystate('users_reg_allowreg', 'users_reg_allowreg_wrap', false);

    // TODO - the following seems to do exactly the same thing as radioswitchdisplaystate call above
    if ($('reg_allowregyes').checked) {
        $('users_reg_allowreg_wrap').hide();
    }

    Event.observe('moderationyes', 'click', users_moderation_onclick, false);
    Event.observe('moderationno', 'click', users_moderation_onclick, false);
    Event.observe('reg_verifyemail2', 'click', users_verification_onclick, false);
    Event.observe('reg_verifyemail0', 'click', users_verification_onclick, false);
    users_orderSwitchDisplayState();

}

function users_reg_allowreg_onchange()
{
    radioswitchdisplaystate('users_reg_allowreg', 'users_reg_allowreg_wrap', false);
}

function users_moderation_onclick()
{
    users_orderSwitchDisplayState();
}

function users_verification_onclick()
{
    users_orderSwitchDisplayState();
}

function users_orderSwitchDisplayState()
{
    var moderationObjGroup = $('users_moderation');
    var verificationObjGroup = $('reg_verifyemail');
    var objCont = $('moderation_order_wrap');

    check_state = moderationObjGroup.select('input[type=radio][value="1"]').pluck('checked').any();
    check_state = (check_state && !verificationObjGroup.select('input[type=radio][value="0"]').pluck('checked').any());

    if (check_state == true) {
        if (objCont.getStyle('display') == 'none') {
            if (typeof(Effect) != "undefined") {
                Effect.BlindDown(objCont);
            } else {
                objCont.show();
            }
        }
    } else {
        if (objCont.getStyle('display') != 'none') {
            if (typeof(Effect) != "undefined") {
                Effect.BlindUp(objCont);
            } else {
                objCont.hide();
            }
        }
    }
}
