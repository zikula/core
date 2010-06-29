// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).
Event.observe(window, 'load', function(){
    Event.observe('users_setpass_yes', 'click', users_setpass_onclick, false);
    Event.observe('users_setpass_no', 'click', users_setpass_onclick, false);
    $('users_setpass_container').removeClassName('z-hide');
    $('users_setpass_no_wrap').removeClassName('z-hide');
    users_setpass_onclick();
});

function users_setpass_onclick()
{
    Zikula.radioswitchdisplaystate('users_setpass', 'users_setpass_yes_wrap', true);
    Zikula.radioswitchdisplaystate('users_setpass', 'users_usermustverify_wrap', true);
    Zikula.radioswitchdisplaystate('users_setpass', 'users_setpass_no_wrap', false);
}
