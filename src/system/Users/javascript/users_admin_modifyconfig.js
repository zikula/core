Event.observe(window, 'load', users_modifyconfig_init, false);

function users_modifyconfig_init()
{
    Event.observe('reg_allowregyes', 'click', users_reg_allowreg_onchange, false);
    Event.observe('reg_allowregno', 'click', users_reg_allowreg_onchange, false);
    radioswitchdisplaystate('users_reg_allowreg', 'users_reg_allowreg_wrap', false);

    if ($('reg_allowregyes').checked) {
        $('users_reg_allowreg_wrap').hide();
    }
}

function users_reg_allowreg_onchange()
{
    radioswitchdisplaystate('users_reg_allowreg', 'users_reg_allowreg_wrap', false);
}
