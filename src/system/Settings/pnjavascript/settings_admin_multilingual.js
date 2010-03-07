Event.observe(window, 'load', settings_multilingual_init, false);

function settings_multilingual_init()
{
    Event.observe('language_bc0', 'click', mlsettings_language_bc_onchange, false);
    Event.observe('language_bc1', 'click', mlsettings_language_bc_onchange, false);
    if ( $('language_bc1').checked) {
        $('mlsettings_language_bc_warning').hide();
    }
    
    Event.observe('language_detect0', 'click', mlsettings_language_detect_onchange, false);
    Event.observe('language_detect1', 'click', mlsettings_language_detect_onchange, false);
    if ( $('language_detect0').checked) {
        $('mlsettings_language_detect_warning').hide();
    }
    
}

function mlsettings_language_bc_onchange()
{
    radioswitchdisplaystate('mlsettings_language_bc', 'mlsettings_language_bc_warning', false);
}

function mlsettings_language_detect_onchange()
{
    radioswitchdisplaystate('mlsettings_language_detect', 'mlsettings_language_detect_warning', true);
}

