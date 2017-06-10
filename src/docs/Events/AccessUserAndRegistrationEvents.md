Access, User and Registration Events
====================================

Classes:
    \Zikula\UsersModule\AccessEvents
    \Zikula\UsersModule\RegistrationEvents
    \Zikula\UsersModule\UserEvents

There are MANY events throughout the UsersModule. These have been divided amongst three classes to better organize them.
Because there are so many, they are not documented here. Please see the respective classes for further information.

 - Access Events: Events concerning login/logout/authentication
 - Registration Events: Events concerning user registration
 - User Events: Events concerning user account creation/display/manipulation.

In addition, Hooks are used extensively within the UsersModule. There are also 'hook-like' events that are used
within the UsersModule.

Deprecation Notice
------------------

The following events are deprecated as of Core-1.5.0 and will be removed in Core-2.0:

  - \Zikula\UsersModule\RegistrationEvents::NEW_FORM
  - \Zikula\UsersModule\RegistrationEvents::NEW_VALIDATE
  - \Zikula\UsersModule\RegistrationEvents::NEW_PROCESS
  - \Zikula\UsersModule\RegistrationEvents::MODIFY_FORM
  - \Zikula\UsersModule\RegistrationEvents::MODIFY_VALIDATE
  - \Zikula\UsersModule\RegistrationEvents::MODIFY_PROCESS
  - \Zikula\UsersModule\UserEvents::NEW_FORM
  - \Zikula\UsersModule\UserEvents::NEW_VALIDATE
  - \Zikula\UsersModule\UserEvents::NEW_PROCESS
  - \Zikula\UsersModule\UserEvents::MODIFY_FORM
  - \Zikula\UsersModule\UserEvents::MODIFY_VALIDATE
  - \Zikula\UsersModule\UserEvents::MODIFY_PROCESS

These are all replaced by:

  - \Zikula\UsersModule\UserEvents::EDIT_FORM
  - \Zikula\UsersModule\UserEvents::EDIT_FORM_HANDLE

See their descriptions in the \Zikula\UsersModule\UserEvents class.
