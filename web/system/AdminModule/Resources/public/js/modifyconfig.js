// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).

document.observe('dom:loaded', admin_modifyconfig_init);

function admin_modifyconfig_init()
{
    $('admin_ignoreinstallercheck').observe('click', admin_ignoreinstallercheck_onchange);

    if (!$('admin_ignoreinstallercheck').checked) {
        $('admin_ignoreinstallercheck_warning').hide();
    }
}

function admin_ignoreinstallercheck_onchange()
{
    Zikula.checkboxswitchdisplaystate('admin_ignoreinstallercheck', 'admin_ignoreinstallercheck_warning', true);
}
