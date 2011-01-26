// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).

// Create the Zikula.Users object if needed
Zikula.define('Users.Admin');

// Create the Zikula.Users.Admin.NewUser object
Zikula.Users.Admin.NewUser = {
    /**
     * Initializes the scripts and elements on the form.
     */
    init: function()
    {
        Zikula.Users.Admin.NewUser.setup();
        
        $(Zikula.Users.Admin.NewUser.fieldId.setPassYes).observe('click', Zikula.Users.Admin.NewUser.setpass_onClick);
        $(Zikula.Users.Admin.NewUser.fieldId.setPassNo).observe('click', Zikula.Users.Admin.NewUser.setpass_onClick);
        $(Zikula.Users.Admin.NewUser.fieldId.setPassWrap).removeClassName('z-hide');
        $(Zikula.Users.Admin.NewUser.fieldId.passwordNotSetWrap).removeClassName('z-hide');
        Zikula.Users.Admin.NewUser.setpass_onClick();
    },

    /**
     * Click event handler for the setpass field.
     */
    setpass_onClick: function()
    {
        Zikula.radioswitchdisplaystate(Zikula.Users.Admin.NewUser.fieldId.setPass, Zikula.Users.Admin.NewUser.fieldId.passWrap, true);
        Zikula.radioswitchdisplaystate(Zikula.Users.Admin.NewUser.fieldId.setPass, Zikula.Users.Admin.NewUser.fieldId.passwordIsSetWrap, true);
        Zikula.radioswitchdisplaystate(Zikula.Users.Admin.NewUser.fieldId.setPass, Zikula.Users.Admin.NewUser.fieldId.passwordNotSetWrap, false);
    }
}

// Load and execute the initialization when the DOM is ready. This must be below the definition of the init function!
document.observe("dom:loaded", Zikula.Users.Admin.NewUser.init);
