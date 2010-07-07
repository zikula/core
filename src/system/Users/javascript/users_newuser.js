// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).

// Create the Zikula.Users object if needed
Zikula.define('Users');

// Create the Zikula.Users.ModifyConfig object
Zikula.Users.NewUser = {
    /**
     * Initializes the scripts and elements on the form.
     */
    init: function()
    {
        $('submitnewuser').disabled = true;
        $('submitnewuser').addClassName('z-hide');

        var formfields = Form.getElements("users_newuser");
        for (var i = 0, len = formfields.length; i < len; i++) {
            if ((formfields[i].id != "checkuserajax") && (formfields[i].id != "submitnewuser")) {
                Event.observe(formfields[i], 'click', Zikula.Users.NewUser.lastCheckExpired);
                Event.observe(formfields[i], 'keypress', Zikula.Users.NewUser.lastCheckExpired);
            }
        }

        Event.observe('users_reginfo_uname', 'keyup', function(){$('users_reginfo_uname').value = $('users_reginfo_uname').value.toLowerCase();}, false);
        Event.observe('users_reginfo_email', 'keyup', function(){$('users_reginfo_email').value = $('users_reginfo_email').value.toLowerCase();}, false);

        Element.removeClassName('checkuserajax', 'z-hide');
        Event.observe('checkuserajax', 'click', Zikula.Users.NewUser.callGetRegistrationErrors, false);
    },

    /**
     * Update element statuses when the form goes from any state to "dirty" (something has potentially changed requiring an error check).
     */
    lastCheckExpired: function()
    {
        if (!$('submitnewuser').disabled) {
            $('users_checkmessage').removeClassName('z-hide');
            $('users_validmessage').addClassName('z-hide');
            $('submitnewuser').addClassName('z-hide');
            $('submitnewuser').disabled = true;
        }
    },

    /**
     * Dispatch an AJAX event to pass the form contents through an error checking function and wait for the results.
     */
    callGetRegistrationErrors: function()
    {
        var pars = "module=Users&func=getRegistrationErrors&" + Form.serialize('users_newuser');
        var myAjax = new Ajax.Request(
            document.location.pnbaseURL + "ajax.php",
            {
                method: 'post',
                parameters: pars,
                onComplete: Zikula.Users.NewUser.getRegistrationErrorsResponse
            });
    },

    /**
     * Process an AJAX response after checking the form contents for errors, and display the appropriate error information.
     *
     * @param req The AJAX response information
     */
    getRegistrationErrorsResponse: function(req)
    {
        if (req.status != 200 ) {
            Zikula.ajaxResponseError(req);
            return;
        }
        var json = Zikula.dejsonize(req.responseText);

        Zikula.updateauthids(json.authid);
        $('users_authid').value = json.authid;

        var errorFields = json.fields;
        var errorMessages = json.messages;
        var formfields = Form.getElements("users_newuser");
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

        var errorMessagesList = $('users_errormsgs');
        var errorMessagesDiv = $('users_errormmsgs_div');
        var submitButton = $('submitnewuser');
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
document.observe("dom:loaded", Zikula.Users.NewUser.init);
