// Copyright Zikula Foundation 2011 - license GNU/LGPLv3 (or at your option, any later version).

// Create the Zikula.Users object if needed
Zikula.define('Users');

// Create the Zikula.Users.ModifyConfig object
Zikula.Users.LoginBlock =
{
    init: function()
    {
        if ($('users_loginblock_loginwith_Users') != null) {
            $('users_loginblock_loginwith_Users').observe('submit', Zikula.Users.LoginBlock.onSubmitLoginWithUsers);
        }
    },

    changingLoginBlockFields: function(changeInProgress)
    {
        var loginForm = $('users_loginblock_loginform');
        var subTitle = $('loginblock_h5_no_authmodule');
        if (changeInProgress) {
            $('users_loginblock_waiting').removeClassName('z-hide');
            if (!loginForm.hasClassName('z-hide')) {
                loginForm.addClassName('z-hide');
            }
            if (subTitle.hasClassName('z-hide')) {
                subTitle.removeClassName('z-hide');
                $('loginblock_h5_authmodule').addClassName('z-hide');
            }
        } else {
            $('users_loginblock_waiting').addClassName('z-hide');
            if (loginForm.hasClassName('z-hide')) {
                loginForm.removeClassName('z-hide');
            }
            if (!subTitle.hasClassName('z-hide')) {
                subTitle.addClassName('z-hide');
                $('loginblock_h5_authmodule').removeClassName('z-hide');
            }
        }

        $$('form.users_loginblock_loginwith').each(function(item) {item.removeClassName('z-hide');});
    },

    onSubmitLoginWithUsers: function(event)
    {
        Zikula.Users.LoginBlock.changingLoginBlockFields(true);

        var r = new Zikula.Ajax.Request(
            Zikula.Config.baseURL + "ajax.php?module=Users&func=getLoginBlockFields",
            {
                method: 'post',
                authid: 'users_authid',
                onComplete: Zikula.Users.LoginBlock.getLoginWithUsersResponse
            });

        event.stop();
    },

    getLoginWithUsersResponse: function(req)
    {
        if (!req.isSuccess()) {
            $('users_loginblock_waiting').addClassName('z-hide');
            Zikula.showajaxerror(req.getMessage());
            return;
        }

        var data = req.getData();

        Element.update('users_loginblock_fields', data.content);

        Zikula.Users.LoginBlock.changingLoginBlockFields(false);

        $('users_loginblock_loginwith_Users').addClassName('z-hide');
    }

}

// Load and execute the initialization when the DOM is ready.
// This must be below the definition of the init function!
document.observe("dom:loaded", Zikula.Users.LoginBlock.init);
