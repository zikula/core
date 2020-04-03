---
currentMenu: authentication
---
# Authentication related events

`Zikula\UsersModule\Event\ActiveUserPreCreatedEvent`

- Force the registration to create a 'pending' user instead of a full user.

`Zikula\UsersModule\Event\RegistrationPostSuccessEvent`

- React to a successfully completed registration

`Zikula\UsersModule\Event\RegistrationPostApprovedEvent`

- React to an administrator's wish to force a user to become a 'full' user.

`Zikula\UsersModule\Event\ActiveUserPostDeletedEvent`

- React to the deletion of a user.

`Zikula\UsersModule\Event\RegistrationPostDeletedEvent`

- React to the deletion of a pending user.

`Zikula\UsersModule\Event\UserPreLoginSuccessEvent`

- force the halt of an otherwise successful login and require user action.
