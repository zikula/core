Event.observe(window, 'load', securitycenter_modifyconfig_init, false);

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
    Event.observe('securitycenter_secure_domain', 'click', securitycenter_secure_domain_onchange, false);
    Event.observe('securitycenter_seclevel', 'change', securitycenter_seclevel_onchange, false);
    Event.observe('securitycenter_sessionstoretofile_file', 'click', securitycenter_sessionstoretofile_onchange, false);
    Event.observe('securitycenter_sessionstoretofile_directory', 'click', securitycenter_sessionstoretofile_onchange, false);
    Event.observe('securitycenter_sessionsavepath', 'click', securitycenter_sessionsavepath_onchange, false);
    Event.observe('securitycenter_signcookies_yes', 'click', securitycenter_signcookies_onchange, false);
    Event.observe('securitycenter_signcookies_no', 'click', securitycenter_signcookies_onchange, false);
    Event.observe('securitycenter_sessionname', 'click', securitycenter_sessionname_onchange, false);
// There is no element with the ID - markwest?
//   Event.observe('securitycenter_wheretosavesessions', 'click', securitycenter_wheretosavesessions_onchange, false);
// There is no function 'securitycenter_sitesecureurl_onchange' - markwest?
//    Event.observe('securitycenter_secure_domain', 'change', securitycenter_sitesecureurl_onchange, false);

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
    if ($('securitycenter_sessionstoretofile_file').checked == true) {
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
