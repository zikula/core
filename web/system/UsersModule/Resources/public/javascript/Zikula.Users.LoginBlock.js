// Copyright Zikula Foundation 2011 - license GNU/LGPLv3 (or at your option, any later version).

// Create the Zikula.Users object if needed
Zikula.define('Users');

// Create the Zikula.Users.ModifyConfig object
Zikula.Users.LoginBlock =
{
    init: function()
    {
        if ($('authentication_select_method_form_users_uname') != null) {
            $('authentication_select_method_form_users_uname').observe('submit', function(event){Zikula.Users.LoginBlock.onSubmitSelectAuthenticationMethod(event, 'authentication_select_method_form_users_uname');});
        }
        if ($('authentication_select_method_form_users_email') != null) {
            $('authentication_select_method_form_users_email').observe('submit', function(event){Zikula.Users.LoginBlock.onSubmitSelectAuthenticationMethod(event, 'authentication_select_method_form_users_email');});
        }
    },

    showAjaxInProgress: function()
    {
        // Hide login form
        var elementChangingClass = $('users_loginblock_login_form');
        if (!elementChangingClass.hasClassName('z-hide')) {
            elementChangingClass.addClassName('z-hide');
        }

        // Hide error notice
        elementChangingClass = $('users_loginblock_no_loginformfields');
        if (!elementChangingClass.hasClassName('z-hide')) {
            elementChangingClass.addClassName('z-hide');
        }

        // Unhide heading used when no authentication module is chosen
        elementChangingClass = $('users_loginblock_h5_no_authentication_method');
        if (elementChangingClass.hasClassName('z-hide')) {
            elementChangingClass.removeClassName('z-hide');
        }

        // Hide heading used when authentication module is chosen
        elementChangingClass = $('users_loginblock_h5_authentication_method');
        if (!elementChangingClass.hasClassName('z-hide')) {
            elementChangingClass.addClassName('z-hide');
        }

        // Unhide all authentication method selectors
        $$('form.authentication_select_method').invoke('removeClassName', 'z-hide');

        // Unhide the waiting indicator
        $('users_loginblock_waiting').removeClassName('z-hide');
    },

    showAjaxComplete: function(isError)
    {
        // Unhide waiting indicator
        $('users_loginblock_waiting').addClassName('z-hide');

        var elementChangingClass;
        if (isError) {
            // Hide login form
            elementChangingClass = $('users_loginblock_login_form');
            if (!elementChangingClass.hasClassName('z-hide')) {
                elementChangingClass.addClassName('z-hide');
            }

            // Unhide error notification
            elementChangingClass = $('users_loginblock_no_loginformfields');
            if (elementChangingClass.hasClassName('z-hide')) {
                elementChangingClass.removeClassName('z-hide');
            }

            // Unhide heading used when there is no authentication method selected
            elementChangingClass = $('users_loginblock_h5_no_authentication_method');
            if (elementChangingClass.hasClassName('z-hide')) {
                elementChangingClass.removeClassName('z-hide');
            }

            // Hide heading used when authentication method selected
            elementChangingClass = $('users_loginblock_h5_authentication_method');
            if (!elementChangingClass.hasClassName('z-hide')) {
                elementChangingClass.addClassName('z-hide');
            }
        } else {
            // No error

            // Unhide login form
            elementChangingClass = $('users_loginblock_login_form');
            if (elementChangingClass.hasClassName('z-hide')) {
                elementChangingClass.removeClassName('z-hide');
            }

            // Hide error notification
            elementChangingClass = $('users_loginblock_no_loginformfields');
            if (!elementChangingClass.hasClassName('z-hide')) {
                elementChangingClass.addClassName('z-hide');
            }

            // Hide heading used when there is no authentication method selected
            elementChangingClass = $('users_loginblock_h5_no_authentication_method');
            if (!elementChangingClass.hasClassName('z-hide')) {
                elementChangingClass.addClassName('z-hide');
            }

            // Unhide heading used when authentication method selected
            elementChangingClass = $('users_loginblock_h5_authentication_method');
            if (elementChangingClass.hasClassName('z-hide')) {
                elementChangingClass.removeClassName('z-hide');
            }
        }
    },

    onSubmitSelectAuthenticationMethod: function(event, formId)
    {
        Zikula.Users.LoginBlock.showAjaxInProgress();

        var parameterObj = $(formId).serialize(true);
        parameterObj.form_type = 'loginblock';

        var r = new Zikula.Ajax.Request(
            Zikula.Config.baseURL + 'index.php?module=Users&type=ajax&func=getLoginFormFields',
            {
                parameters: parameterObj,
                onSuccess: Zikula.Users.LoginBlock.getSelectAuthenticationMethodResponse,
                onFailure: Zikula.Users.LoginBlock.selectAuthenticationMethodResponseFailure
            });

        // Prevent form from sumitting itself. We just did it here.
        event.stop();
    },

    getSelectAuthenticationMethodResponse: function(req)
    {
        var data = req.getData();

        // Zikula.Ajax.Request calls onSuccess and onFailure if the AJAX operation times out.
        if (data) {
            // No timeout
            Element.update('users_loginblock_fields', data.content);
            $('users_loginblock_selected_authentication_module').setValue(data.modname);
            $('users_loginblock_selected_authentication_method').setValue(data.method);

            if (data.method !== false) {
                // Hide the chosen authentication method in the list
                $('authentication_select_method_form_' + data.modname.toLowerCase() + '_' + data.method.toLowerCase()).addClassName('z-hide');
            }

            Zikula.Users.LoginBlock.showAjaxComplete(false);
        } else {
            Zikula.Users.LoginBlock.showAjaxComplete(true);
        }
    },

    selectAuthenticationMethodResponseFailure: function(req)
    {
        // Zikula.Ajax.Request calls both onSuccess and onFailure if the AJAX operation times out.
        Zikula.Users.LoginBlock.showAjaxComplete(true);
        if (req.readyState != 0) {
            // readyState 0: uninitialized. This is probably a timeout.
            Zikula.showajaxerror(req.getStatus() + ': ' + req.getMessage());
        }
    }

}

// Load and execute the initialization when the DOM is ready.
// This must be below the definition of the init function!
document.observe("dom:loaded", Zikula.Users.LoginBlock.init);
