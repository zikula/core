// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).

// Create the Zikula.Users object if needed
Zikula.define('Users');

// Create the Zikula.Users.ModifyConfig object
Zikula.Users.AdminModifyRegistration = {
    /**
     * Initializes the scripts and elements on the form.
     */
    init: function()
    {
        $('submitchanges').disabled = true;
        $('submitchanges').addClassName('z-hide');

        var formfields = Form.getElements("users_modifyregistration");
        for (var i = 0, len = formfields.length; i < len; i++) {
            if ((formfields[i].id != "checkuserajax") && (formfields[i].id != "submitchanges")) {
                Event.observe(formfields[i], 'click', Zikula.Users.AdminModifyRegistration.lastCheckExpired);
                Event.observe(formfields[i], 'keypress', Zikula.Users.AdminModifyRegistration.lastCheckExpired);
            }
        }

        // The following two observers force the user name and e-mail address to lower case.
        Event.observe('users_reginfo_uname', 'keyup', function(){$('users_reginfo_uname').value = $('users_reginfo_uname').value.toLocaleLowerCase();}, false);
        Event.observe('users_reginfo_email', 'keyup', function(){$('users_reginfo_email').value = $('users_reginfo_email').value.toLocaleLowerCase();}, false);

        Element.removeClassName('checkuserajax', 'z-hide');
        Event.observe('checkuserajax', 'click', Zikula.Users.AdminModifyRegistration.callGetRegistrationErrors, false);
    },

    /**
     * Fired on any change to any form element that might cause the form's contents to be rechecked for errors.
     */
    lastCheckExpired: function()
    {
        if (!$('submitchanges').disabled) {
            $('users_checkmessage').removeClassName('z-hide');
            $('users_validmessage').addClassName('z-hide');
            $('submitchanges').addClassName('z-hide');
            $('submitchanges').disabled = true;
        }
    },

    /**
     * Initiate an AJAX call to check the form contents for errors and wait for a response.
     */
    callGetRegistrationErrors: function()
    {
        var pars = "module=Users&func=getRegistrationErrors&" + Form.serialize('users_modifyregistration');
        var myAjax = new Ajax.Request(
            document.location.pnbaseURL + "ajax.php",
            {
                method: 'post',
                parameters: pars,
                onComplete: Zikula.Users.AdminModifyRegistration.responseGetRegistrationErrors
            });
    },

    /**
     * Process the AJAX response after asking for the form contents to be checked for errors, displaying any error information.
     *
     *@param req The AJAX response object.
     */
    responseGetRegistrationErrors: function(req)
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
}

// Load and execute the initialization when the DOM is ready. This must be below the definition of the init function!
document.observe("dom:loaded", Zikula.Users.AdminModifyRegistration.init);
