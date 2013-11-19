// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).

// Create the Zikula.Users object if needed
Zikula.define('Users');

// Create the Zikula.Users.ModifyConfig object
Zikula.Users.NewUser = {
    /**
     * Holds references to the getRegistrationErrorsResponse() equivalents for validator handlers.
     */
    validatorHandlers : $H(),
    
    /**
     * Initializes the scripts and elements on the form.
     */
    init: function()
    {
        Zikula.Users.NewUser.setup();
        
        var submitElement = $(Zikula.Users.NewUser.fieldId.submit)
        submitElement.disabled = true;
        submitElement.addClassName('hide');
        
        var formfields = Form.getElements(Zikula.Users.NewUser.formId);
        for (var i = 0, len = formfields.length; i < len; i++) {
            if ((formfields[i].id != Zikula.Users.NewUser.fieldId.checkUser) && (formfields[i].id != Zikula.Users.NewUser.fieldId.submit)) {
                formfields[i].observe('click', Zikula.Users.NewUser.lastCheckExpired);
                formfields[i].observe('keypress', Zikula.Users.NewUser.lastCheckExpired);
            }
        }

        $(Zikula.Users.NewUser.fieldId.userName).observe('blur', function(){
            var userNameElement = $(Zikula.Users.NewUser.fieldId.userName)
            userNameElement.value = userNameElement.value.toLowerCase();
        });
        
        $(Zikula.Users.NewUser.fieldId.email).observe('blur', function(){
            var emailElement = $(Zikula.Users.NewUser.fieldId.email)
            emailElement.value = emailElement.value.toLowerCase();
        });

        var checkUserElement = $(Zikula.Users.NewUser.fieldId.checkUser);
        checkUserElement.removeClassName('hide');
        checkUserElement.observe('click', Zikula.Users.NewUser.callGetRegistrationErrors);
    },

    /**
     * Update element statuses when the form goes from any state to "dirty" (something has potentially changed requiring an error check).
     */
    lastCheckExpired: function()
    {
        var submitElement = $(Zikula.Users.NewUser.fieldId.submit);
        var checkUserElement = $(Zikula.Users.NewUser.fieldId.checkUser);
        var checkMessageElement = $(Zikula.Users.NewUser.fieldId.checkMessage);
        var validMessageElement = $(Zikula.Users.NewUser.fieldId.validMessage);
        
        if (checkMessageElement.hasClassName('hide')) {
            checkMessageElement.removeClassName('hide');
        }
        if (!validMessageElement.hasClassName('hide')) {
            validMessageElement.addClassName('hide');
        }
        if (!submitElement.hasClassName('hide')) {
            submitElement.addClassName('hide');
        }
        submitElement.disabled = true;
        if (checkUserElement.hasClassName('hide')) {
            checkUserElement.removeClassName('hide');
        }
        checkUserElement.disabled = false;
    },
    
    showAjaxInProgress: function()
    {
        var submitElement = $(Zikula.Users.NewUser.fieldId.submit);
        var checkUserElement = $(Zikula.Users.NewUser.fieldId.checkUser);
        var indicatorElement = $(Zikula.Users.NewUser.formId + '_ajax_indicator');
        var checkMessageElement = $(Zikula.Users.NewUser.fieldId.checkMessage);
        var validMessageElement = $(Zikula.Users.NewUser.fieldId.validMessage);
        
        if (indicatorElement.hasClassName('hide')) {
            indicatorElement.removeClassName('hide');
        }
        
        if (checkMessageElement.hasClassName('hide')) {
            checkMessageElement.removeClassName('hide');
        }
        
        if (!checkMessageElement.hasClassName(' hidden ')) {
            checkMessageElement.addClassName(' hidden ');
        }
        
        if (!validMessageElement.hasClassName('hide')) {
            validMessageElement.addClassName('hide');
        }
        
        if (!submitElement.hasClassName('hide')) {
            submitElement.addClassName('hide');
        }
        
        submitElement.disabled = true;
        
        if (!checkUserElement.hasClassName('hide')) {
            checkUserElement.addClassName('hide');
        }
        
        checkUserElement.disabled = true;
    },

    showAjaxComplete: function(hasError)
    {
        var submitElement = $(Zikula.Users.NewUser.fieldId.submit);
        var checkUserElement = $(Zikula.Users.NewUser.fieldId.checkUser);
        var indicatorElement = $(Zikula.Users.NewUser.formId + '_ajax_indicator');
        var checkMessageElement = $(Zikula.Users.NewUser.fieldId.checkMessage);
        var validMessageElement = $(Zikula.Users.NewUser.fieldId.validMessage);
        
        if (!indicatorElement.hasClassName('hide')) {
            indicatorElement.addClassName('hide');
        }
        
        if (hasError) {
            if (checkMessageElement.hasClassName(' hidden ')) {
                checkMessageElement.removeClassName(' hidden ');
            }
            if (checkMessageElement.hasClassName('hide')) {
                checkMessageElement.removeClassName('hide');
            }
            if (!validMessageElement.hasClassName('hide')) {
                validMessageElement.addClassName('hide');
            }
            if (!submitElement.hasClassName('hide')) {
                submitElement.addClassName('hide');
            }
            submitElement.disabled = true;
            if (checkUserElement.hasClassName('hide')) {
                checkUserElement.removeClassName('hide');
            }
            checkUserElement.disabled = false;
        } else {
            if (checkMessageElement.hasClassName(' hidden ')) {
                checkMessageElement.removeClassName(' hidden ');
            }
            if (!checkMessageElement.hasClassName('hide')) {
                checkMessageElement.addClassName('hide');
            }
            if (validMessageElement.hasClassName('hide')) {
                validMessageElement.removeClassName('hide');
            }
            if (submitElement.hasClassName('hide')) {
                submitElement.removeClassName('hide');
            }
            submitElement.disabled = false;
            if (!checkUserElement.hasClassName('hide')) {
                checkUserElement.addClassName('hide');
            }
            checkUserElement.disabled = true;
        }
    },
    
    /**
     * Adds a validator handler to the list of handlers for the given area name.
     */
    addValidatorHandler: function(areaName, functionReference)
    {
        Zikula.Users.NewUser.validatorHandlers.set(areaName, functionReference);
    },
    
    /**
     * Dispatch an AJAX event to pass the form contents through an error checking function and wait for the results.
     */
    callGetRegistrationErrors: function()
    {
        Zikula.Users.NewUser.showAjaxInProgress();
        
        var pars = $(Zikula.Users.NewUser.formId).serialize(true);
        new Zikula.Ajax.Request(
            Zikula.Config.baseURL + "index.php?module=ZikulaUsersModule&type=ajax&func=getRegistrationErrors",
            {
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
        if (!req.isSuccess()) {
            Zikula.Users.NewUser.showAjaxComplete(true);
            Zikula.showajaxerror(req.getMessage());
            return;
        }
        
        var data = req.getData();
        
        if (!data) {
            // A timeout
            Zikula.Users.NewUser.showAjaxComplete(true);
            return;
        }
        
        $$('div#z-maincontent>div.alert.alert-danger').each(function(item) {
            if (!item.hasClassName('hide')) {
                item.addClassName('hide')
            }
        });
        
        $(Zikula.Users.NewUser.formId).getElements().each(function(element, index){
            element.removeClassName('form-error');
        });
        
        $$('#' + Zikula.Users.NewUser.formId + ' .alert.alert-danger').each(function(element, index){
            element.update();
            if (!element.hasClassName('hide')) {
                element.addClassName('hide');
            }
        });
        
        if (data.errorMessagesCount > 0) {
            var errorMessages = $A(data.errorMessages);
            var errorMessagesDiv = $(Zikula.Users.NewUser.formId + '_errormsgs');
            
            errorMessagesDiv.update();
            errorMessages.each(function(item, index) {
                if (index > 0) {
                    errorMessagesDiv.insert('<hr />');
                }
                errorMessagesDiv.insert(item);
            });
            
            errorMessagesDiv.removeClassName('hide');
        }

        if (data.errorFieldsCount > 0) {
            var errorFields = $H(data.errorFields);
            errorFields.each(function(pair){
                var element = $(Zikula.Users.NewUser.formId + '_' + pair.key);
                if (element) {
                    element.addClassName('form-error');
                }

                element = $(Zikula.Users.NewUser.formId + '_' + pair.key + '_error');
                element.update(pair.value);
                element.removeClassName('hide');

                var fieldset = element.up('fieldset');
                if (fieldset.hasClassName('hide')) {
                    fieldset.removeClassName('hide');
                }
            });
        }
        
        if (data.validatorErrorsCount > 0) {
            var validatorErrors = $H(data.validatorErrors);
            validatorErrors.each(function(pair) {
                var handlerFunction = Zikula.Users.NewUser.validatorHandlers.get(pair.key);
                
                if (handlerFunction) {
                    var errorInfo = $H(pair.value);
                    handlerFunction(errorInfo.get('errorFieldsCount'), $H(errorInfo.get('errorFields')));
                }
            });
        }

        if ((data.errorMessagesCount > 0) || (data.errorFieldsCount > 0) || (data.validatorErrorsCount > 0)) {
            Zikula.Users.NewUser.showAjaxComplete(true);
            Zikula.Users.NewUser.lastCheckExpired();
            
            location.hash = Zikula.Users.NewUser.formId + '_errormsgs';
        } else {
            Zikula.Users.NewUser.showAjaxComplete(false);
        }
    }
}

// Load and execute the initialization when the DOM is ready. This must be below the definition of the init function!
document.observe("dom:loaded", Zikula.Users.NewUser.init);
