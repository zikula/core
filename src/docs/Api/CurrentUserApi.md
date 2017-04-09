CurrentUserApi
==============

classname: \Zikula\UsersModule\Api\CurrentUserApi

service id = "zikula_users_module.current_user"

The CurrentUserApi can be used to obtain the properties of the user operating at runtime. Any property of the UserEntity
is available. For example, to obtain the User id (`uid`) of the current user:

    $this->get('zikula_users_module.current_user')->get('uid')

Or to check if the current user is logged in:

    if ($this->get('zikula_users_module.current_user')->isLoggedIn()) {
        return $this->redirectToRoute('zikulausersmodule_account_menu');
    }

The class makes the following methods available:

    - isLoggedIn()
    - get($key)

The `get` method can be used to acquire any property of the UserEntity.
