// Copyright Zikula Foundation 2011 - license GNU/LGPLv3 (or at your option, any later version).

// Create the Zikula.Users object if needed
Zikula.define('Users');

// Create the Zikula.Users.ModifyConfig object
Zikula.Users.LoginBlock =
{
    init: function()
    {
        if ($('users_block_loginwith_Users') != null) {
            $('users_block_loginwith_Users').observe('submit', Zikula.Users.LoginBlock.onSubmitLoginWithUsers());
        }
    },

    onSubmitLoginWithUsers: function()
    {
        new Zikula.Ajax.Request(
            Zikula.Config.baseURL + "ajax.php?module=Users&func=getLoginBlockFields",
            {
                method: 'post',
                authid: 'users_authid',
                onComplete: Zikula.Users.LoginBlock.getLoginWithUsersResponse
            });

        // Return false so the form does not submit.
        return false;
    },

    getLoginWithUsersResponse: function(req)
    {
        if (!req.isSuccess()) {
            Zikula.showajaxerror(req.getMessage());
            return;
        }

        var data = req.getData();

    }

}

// Load and execute the initialization when the DOM is ready. This must be below the definition of the init function!
document.observe("dom:loaded", Zikula.Users.LoginBlock.init);
