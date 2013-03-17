// Copyright Zikula Foundation 2010 - license GNU/LGPLv2.1 (or at your option, any later version).

document.observe('dom:loaded', securitycenter_modifyconfig_init);

function securitycenter_modifyconfig_init()
{
    if ($('securitycenter_sessionstoretofile_file').checked == true) {
        $('securitycenter_sessionsavepath_container').show();
    } else {
        $('securitycenter_sessionsavepath_container').hide();
    }
    $('securitycenter_sessionnamewarning_container').hide();
    $('securitycenter_wheretosavesessionswarning_container').hide();
    $('securitycenter_sitesecureurl_container').hide();

    $('securitycenter_secure_domain').observe('click', securitycenter_secure_domain_onchange);
    $('securitycenter_seclevel').observe('change', securitycenter_seclevel_onchange);
    $('securitycenter_sessionstoretofile_file').observe('click', securitycenter_sessionstoretofile_onchange);
    $('securitycenter_sessionstoretofile_directory').observe('click', securitycenter_sessionstoretofile_onchange);
    $('securitycenter_sessionsavepath').observe('click', securitycenter_sessionsavepath_onchange);
    $('securitycenter_signcookies_yes').observe('click', securitycenter_signcookies_onchange);
    $('securitycenter_signcookies_no').observe('click', securitycenter_signcookies_onchange);
    $('securitycenter_sessionname').observe('click', securitycenter_sessionname_onchange);
    $('securitycenter_sessioncsrftokenonetime_onetime').observe('click', securitycenter_sessioncsrftokenonetime_onchange);
    $('securitycenter_sessioncsrftokenonetime_persession').observe('click', securitycenter_sessioncsrftokenonetime_onchange);

    securitycenter_sessioncsrftokenonetime_onchange();
    securitycenter_sessionname_onchange();    
    securitycenter_wheretosavesessions_onchange();
    securitycenter_secure_domain_onchange();
    securitycenter_seclevel_onchange();
    securitycenter_sessionstoretofile_onchange();
    securitycenter_signcookies_onchange();
}

function securitycenter_sessionname_onchange()
{
    $('securitycenter_sessionnamewarning_container').show();
}

function securitycenter_wheretosavesessions_onchange()
{
    $('securitycenter_wheretosavesessionswarning_container').show();
}

function securitycenter_secure_domain_onchange()
{
    $('securitycenter_sitesecureurl_container').show();
}

function securitycenter_sessionsavepath_onchange()
{
    $('securitycenter_sessionfilessavepathwarning_container').hide();
}

function securitycenter_sessioncsrftokenonetime_onchange()
{
    if ($('securitycenter_sessioncsrftokenonetime_onetime').checked == true) {
        $('securitycenter_sessioncsrftokenonetime_container').show();
    } else {
        $('securitycenter_sessioncsrftokenonetime_container').hide();
    }
}

function securitycenter_seclevel_onchange()
{
    if ($('securitycenter_seclevel').value == 'Medium') {
        $('securitycenter_seclevel_secmeddays_container').show();
        $('securitycenter_seclevel_secinactivemins_container').show();
    } else if ($('securitycenter_seclevel').value == 'High') {
        $('securitycenter_seclevel_secmeddays_container').hide();
        $('securitycenter_seclevel_secinactivemins_container').show();
    } else {
        $('securitycenter_seclevel_secmeddays_container').hide();
        $('securitycenter_seclevel_secinactivemins_container').hide();
    }
}

function securitycenter_sessionstoretofile_onchange()
{
    if ($('securitycenter_sessioncsrftokenonetime_onetime').checked == true) {
        $('securitycenter_sessionsavepath_container').show();
    } else {
        $('securitycenter_sessionsavepath_container').hide();
    }
}

function securitycenter_signcookies_onchange()
{
    if ($('securitycenter_signcookies_yes').checked == true) {
        $('securitycenter_signingkey_container').show();
    } else {
        $('securitycenter_signingkey_container').hide();
    }
}
