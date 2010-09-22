// Copyright Zikula Foundation 2009 - license GNU/LGPLv2.1 (or at your option, any later version).

Event.observe(window, 'load', mailer_testconfig_init);

function mailer_testconfig_init()
{
     $('mailer_msgtype_text').observe('click', mailer_msgtype_onclick);
     $('mailer_msgtype_html').observe('click', mailer_msgtype_onclick);
     $('mailer_msgtype_multipart').observe('click', mailer_msgtype_onclick);
     mailer_msgtype_onclick();
}

function mailer_msgtype_onclick()
{
//    var typeText = $('mailer_msgtype_text');
    var typeHTML = $('mailer_msgtype_html');
    var typeMultipart = $('mailer_msgtype_multipart');

    var flagHTML = $('mailer_html');
    var labelHTML = $('mailer_body_html');
    var divAltBody = $('mailer_altbody_div');

    if (typeHTML.checked) {
        flagHTML.value = 1;
    } else {
        flagHTML.value = 0;
    }

    if (typeMultipart.checked) {
        labelHTML.show();
        divAltBody.show();
    } else {
        labelHTML.hide();
        divAltBody.hide();
    }
}
