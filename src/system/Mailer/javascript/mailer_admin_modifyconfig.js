// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).

document.observe('dom:loaded', mailer_modifyconfig_init);

function mailer_modifyconfig_init()
{
     $('mailer_mailertype').observe('change', mailer_transport_onchange);
     $('mailer_smtpauth').observe('change', mailer_smtpauth_onchange);
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
    Zikula.checkboxswitchdisplaystate('mailer_smtpauth', 'mailer_smtp_authentication', true);
}
