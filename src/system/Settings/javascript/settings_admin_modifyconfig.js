// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).

Event.observe(window, 'load', settings_modifyconfig_init);

function settings_modifyconfig_init()
{
    $('settings_siteoff_yes').observe('click', settings_disablesite_onchange);
    $('settings_siteoff_no').observe('click', settings_disablesite_onchange);
    $('settings_shorturls_yes').observe('click', settings_shorturls_onchange);
    $('settings_shorturls_no').observe('click', settings_shorturls_onchange);

    $$('.z_texpand').each(function(el) {
        new Texpand(el, {autoShrink: false, shrinkOnBlur: false, expandOnFocus: false, expandOnLoad: true });
    });

    if ($('settings_siteoff_no').checked) {
        $('settings_siteoff_container').hide();
    }
    if ($('settings_shorturls_no').checked) {
        $('settings_shorturls_container').hide();
    }
}

function settings_disablesite_onchange()
{
    Zikula.radioswitchdisplaystate('settings_siteoff', 'settings_siteoff_container', true);
}

function settings_shorturls_onchange()
{
    Zikula.radioswitchdisplaystate('settings_shorturls', 'settings_shorturls_container', true);
}
