// Copyright Zikula Foundation 2010 - license GNU/LGPLv2.1 (or at your option, any later version).
 
document.observe('dom:loaded',securitycenter_allowedhtml_init);

function securitycenter_allowedhtml_init()
{
    $('toggle_notallowed').observe('click', function(e){
        Zikula.toggleInput('.notallowed_radio', true);
    });
    $('toggle_allowed').observe('click', function(e){
        Zikula.toggleInput('.allowed_radio', true);
    });
    $('toggle_allowedwith').observe('click', function(e){
        Zikula.toggleInput('.allowedwith_radio', true);
    });

    $$('.notallowed_radio, .allowed_radio, .allowedwith_radio').invoke('observe','click',securitycenter_allowedhtml_check);
}

function securitycenter_allowedhtml_check(event) {
    var type = event.element().className,
        status = $$('.' + type).pluck('checked').all();
    Zikula.toggleInput('#toggle_notallowed, #toggle_allowed, #toggle_allowedwith',false);
    $('toggle_' + type.replace('_radio','')).checked = status;
}