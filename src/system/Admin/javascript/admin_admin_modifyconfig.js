// Copyright Zikula Foundation 2009 - license GNU/LGPLv2.1 (or at your option, any later version).

Event.observe(window, 'load', admin_modifyconfig_init, false);

function admin_modifyconfig_init()
{
    Event.observe('admin_ignoreinstallercheck', 'click', admin_ignoreinstallercheck_onchange, false);

    if ( !$('admin_ignoreinstallercheck').checked) {
        $('admin_ignoreinstallercheck_warning').hide();
    }
}

function admin_ignoreinstallercheck_onchange()
{
    checkboxswitchdisplaystate('admin_ignoreinstallercheck', 'admin_ignoreinstallercheck_warning', true);
}
