Authentication Related Events
=============================

Zikula\UsersModule\RegistrationEvents::FULL_USER_CREATE_VETO

 - Force the registration to create a 'pending' user instead of a full user.

Zikula\UsersModule\RegistrationEvents::REGISTRATION_SUCCEEDED

 - React to a successfully completed registration

Zikula\UsersModule\RegistrationEvents::FORCE_REGISTRATION_APPROVAL

 - React to an administrator's wish to force a user to become a 'full' user.

Zikula\UsersModule\UserEvents::DELETE_ACCOUNT

 - React to the deletion of a user.

Zikula\UsersModule\RegistrationEvents::DELETE_REGISTRATION

 - React to the deletion of a pending user.

Zikula\UsersModule\AccessEvents::LOGIN_VETO

 - force the halt of an otherwise successful login and require user action.
