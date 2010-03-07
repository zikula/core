Event.observe(window, 'load', settings_modifyconfig_init, false);

function settings_modifyconfig_init()
{
    Event.observe('settings_siteoff_yes', 'click', settings_disablesite_onchange, false);
    Event.observe('settings_siteoff_no', 'click', settings_disablesite_onchange, false);
    Event.observe('settings_shorturls_yes', 'click', settings_shorturls_onchange, false);
    Event.observe('settings_shorturls_no', 'click', settings_shorturls_onchange, false);
    Event.observe('settings_shorturlstype_directory', 'click', settings_shorturls_type_onchange, false);
    Event.observe('settings_shorturlstype_file', 'click', settings_shorturls_type_onchange, false);

    $$('.z_texpand').each(function(el){
      new Texpand(el, {autoShrink: true, shrinkOnBlur:false, expandOnFocus: false, expandOnLoad: true });
    });

    if ( $('settings_siteoff_no').checked) {
        $('settings_siteoff_container').hide();
    }
    if ( $('settings_shorturls_no').checked) {
        $('settings_shorturls_container').hide();
    }
    settings_shorturls_type_onchange();
}

function settings_disablesite_onchange()
{
    radioswitchdisplaystate('settings_siteoff', 'settings_siteoff_container', true);
}

function settings_shorturls_onchange()
{
    radioswitchdisplaystate('settings_shorturls', 'settings_shorturls_container', true);
}

function settings_shorturls_type_onchange()
{
    if ( $('settings_shorturlstype_file').checked == true) {
        $('settings_shorturlsext_container').show();
        $('settings_shorturlsstripentrypoint_container').hide();
        $('settings_shorturlsseparator_container').hide();
        $('settings_shorturls_defaultmodule_container').hide();
    } else {
        $('settings_shorturlsext_container').hide();
        $('settings_shorturlsstripentrypoint_container').show();
        $('settings_shorturlsseparator_container').show();
        $('settings_shorturls_defaultmodule_container').show();
    }
}
