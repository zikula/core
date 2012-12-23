// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).

document.observe('dom:loaded', mailer_testconfig_init);
//Event.observe(window, 'load', mailer_testconfig_init);

function mailer_testconfig_init()
{
    $('message_type').on('click', 'input', mailer_msgtype_onclick);
     mailer_msgtype_onclick();
}

function mailer_msgtype_onclick()
{
    var radio = $$('[name=msgtype]:checked'),
        type = radio && radio[0] ? radio[0].getValue() : null,
        htmlBody = $('mailer_body_div'),
        textBody = $('mailer_textbody_div');

    switch(type) {
        case 'html':
            htmlBody.show();
            textBody.hide();
            break;
        case 'multipart':
            htmlBody.show();
            textBody.show();
            break;
        default:
            htmlBody.hide();
            textBody.show();
    }
}
