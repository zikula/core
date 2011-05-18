// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).

Event.observe(window, 'load', settings_modifyconfig_init);

function settings_modifyconfig_init()
{
    $('settings_startpage').observe('change', settings_startpage_nostartpage);
    $('settings_siteoff_yes').observe('click', settings_disablesite_onchange);
    $('settings_siteoff_no').observe('click', settings_disablesite_onchange);
    $('settings_shorturls_yes').observe('click', settings_shorturls_onchange);
    $('settings_shorturls_no').observe('click', settings_shorturls_onchange);

    if ($F('settings_startpage') == '') {
        $('settings_startpage_container').hide();
    }

    if ($('settings_siteoff_no').checked) {
        $('settings_siteoff_container').hide();
    }
    if ($('settings_shorturls_no').checked) {
        $('settings_shorturls_container').hide();
    }
}

function settings_startpage_nostartpage()
{
    var tmpobj = $('settings_startpage_container');

    if ($F('settings_startpage') == '' && tmpobj.getStyle('display') == 'block') {
        if (typeof(Effect) != "undefined") {
            Effect.BlindUp(tmpobj);
        } else {
            tmpobj.hide();
        }
    } else if (tmpobj.getStyle('display') == 'none') {
        if (typeof(Effect) != "undefined") {
            Effect.BlindDown(tmpobj);
        } else {
            tmpobj.show();
        }
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
