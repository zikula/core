// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).

// Create the Zikula.Users.AdminNewUser object
if (typeof(Zikula.Users.AdminNewUser) == 'undefined') {
    Zikula.Users.AdminNewUser = {};
}

// Create the Zikula object if needed
if (typeof(Zikula) == 'undefined') {
    Zikula = {};
}

// Create the Zikula.Users object if needed
if (typeof(Zikula.Users) == 'undefined') {
    Zikula.Users = {};
}

// Create the Zikula.Users.ModifyConfig object
Zikula.Users.AdminNewUser = {
    /**
     * Initializes the scripts and elements on the form.
     */
    init: function()
    {
        Event.observe('users_setpass_yes', 'click', Zikula.Users.AdminNewUser.setpass_onClick, false);
        Event.observe('users_setpass_no', 'click', Zikula.Users.AdminNewUser.setpass_onClick, false);
        $('users_setpass_container').removeClassName('z-hide');
        $('users_setpass_no_wrap').removeClassName('z-hide');
        Zikula.Users.AdminNewUser.setpass_onClick();
    },

    /**
     * Click event handler for the setpass field.
     */
    setpass_onClick: function()
    {
        Zikula.radioswitchdisplaystate('users_setpass', 'users_setpass_yes_wrap', true);
        Zikula.radioswitchdisplaystate('users_setpass', 'users_usermustverify_wrap', true);
        Zikula.radioswitchdisplaystate('users_setpass', 'users_setpass_no_wrap', false);
    }
}

// Load and execute the initialization when the DOM is ready. This must be below the definition of the init function!
document.observe("dom:loaded", Zikula.Users.AdminNewUser.init);
