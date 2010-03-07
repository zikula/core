Event.observe(window, 'load', mailer_modifyconfig_init, false);

function mailer_modifyconfig_init()
{
     Event.observe('mailer_mailertype', 'change', mailer_transport_onchange, false);
     Event.observe('mailer_smtpauth', 'change', mailer_smtpauth_onchange, false);
     mailer_transport_onchange();
     mailer_smtpauth_onchange();
}

function mailer_transport_onchange()
{
    var mailtransport = $('mailer_mailertype')

    if ( mailtransport.value == '4') {
        $('mailer_smtpsettings').show();
    } else {
        $('mailer_smtpsettings').hide();
    }
    if ( mailtransport.value == '2') {
        $('mailer_sendmailsettings').show();
    } else {
        $('mailer_sendmailsettings').hide();
    }
}

function mailer_smtpauth_onchange()
{
    checkboxswitchdisplaystate('mailer_smtpauth', 'mailer_smtp_authentication', true);
}