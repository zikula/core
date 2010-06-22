// Copyright Zikula Foundation 2009 - license GNU/LGPLv2.1 (or at your option, any later version).

Event.observe(window, 'load', function(){
    $('submitchanges').disabled = true;
    $('submitchanges').addClassName('z-hide');

    var formfields = Form.getElements("users_modifyregistration");
    for (var i = 0, len = formfields.length; i < len; i++) {
        if ((formfields[i].id != "checkuserajax") && (formfields[i].id != "submitchanges")) {
            Event.observe(formfields[i], 'click', users_lastcheckexpired);
            Event.observe(formfields[i], 'keypress', users_lastcheckexpired);
        }
    }

    Event.observe('users_reginfo_uname', 'keyup', function(){$('users_reginfo_uname').value = $('users_reginfo_uname').value.toLowerCase();}, false);
    Event.observe('users_reginfo_email', 'keyup', function(){$('users_reginfo_email').value = $('users_reginfo_email').value.toLowerCase();}, false);

    Element.removeClassName('checkuserajax', 'z-hide');
    Event.observe('checkuserajax', 'click', function(){callusercheck()}, false);
});

function users_lastcheckexpired()
{
    if (!$('submitchanges').disabled) {
        $('users_checkmessage').removeClassName('z-hide');
        $('users_validmessage').addClassName('z-hide');
        $('submitchanges').addClassName('z-hide');
        $('submitchanges').disabled = true;
    }
}

/**
 * User Check call
 *
 *@ no param
 *@return none;
 *@author Frank Chestnut
 */
function callusercheck()
{
    var pars = "module=Users&func=getRegistrationErrors&" + Form.serialize('users_modifyregistration');
    var myAjax = new Ajax.Request(
        document.location.pnbaseURL + "ajax.php",
        {
            method: 'post',
            parameters: pars,
            onComplete: checkuser_response
        });
}

/**
 * Ajax response function for checking the user registration information: simply shows a text
 *
 *@params none;
 *@return none;
 *@author Frank Chestnut
 */
function checkuser_response(req)
{
    if (req.status != 200 ) {
        Zikula.showajaxerror(req.responseText);
        return;
    }
    var json = Zikula.dejsonize(req.responseText);

    Zikula.updateauthids(json.authid);
    $('users_authid').value = json.authid;

    var errorFields = json.fields;
    var errorMessages = json.messages;
    var formfields = Form.getElements("users_modifyregistration");
    var field = null;
    var fieldWrapper = null;
    // Resetting before going further
    for (var i = 0, len = formfields.length; i < len; i++) {
        if (formfields[i].type == 'checkbox') {
            fieldWrapper = $(formfields[i].id + '_field');
            if ((typeof(fieldWrapper) != undefined) && (fieldWrapper != null)) {
                Element.removeClassName(fieldWrapper, 'errorrequired');
            }
        } else {
            Element.removeClassName(formfields[i], 'errorrequired');
        }
    }

    for (i = 0, len = errorFields.length; i < len; i++) {
        field = $(errorFields[i]);
        if (field.type == 'checkbox') {
            fieldWrapper = field.id + '_field';
            if ((typeof(fieldWrapper) != undefined) && (fieldWrapper != null)) {
                Element.addClassName(fieldWrapper, 'errorrequired');
            }
        } else {
            Element.addClassName(errorFields[i], 'errorrequired');
        }
    }

    var errorMessagesList = $('users_errormessages');
    var errorMessagesDiv = $('users_errormessages_div');
    var submitButton = $('submitchanges');
    if (errorMessagesList.childElementCount > 0) {
        errorMessagesList.childElements().each(function(item){item.remove()});
    }
    if (errorMessages.length > 0) {
        submitButton.disabled = true;
        submitButton.addClassName('z-hide');
        for (i = 0, len = errorMessages.length; i < len; i++) {
            errorMessagesList.insert('<li>'+errorMessages[i]+'</li>');
        }
        errorMessagesDiv.removeClassName('z-hide');
        location.hash = 'users_formtop';
    } else {
        submitButton.disabled = false;
        submitButton.removeClassName('z-hide');
        if (!errorMessagesDiv.hasClassName('z-hide')) {
            errorMessagesDiv.addClassName('z-hide');
        }
        $('users_checkmessage').addClassName('z-hide');
        $('users_validmessage').removeClassName('z-hide');
    }
}
