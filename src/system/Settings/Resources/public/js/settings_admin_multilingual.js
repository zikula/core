// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).

document.observe('dom:loaded', settings_multilingual_init);

function settings_multilingual_init()
{
    $('language_detect0').observe('click', mlsettings_language_detect_onchange);
    $('language_detect1').observe('click', mlsettings_language_detect_onchange);

    if ($('language_detect0').checked) {
        $('mlsettings_language_detect_warning').hide();
    }
}

function mlsettings_language_detect_onchange()
{
    Zikula.radioswitchdisplaystate('mlsettings_language_detect', 'mlsettings_language_detect_warning', true);
}

