---
currentMenu: developer-events
---
# Access, User and Registration events

The following classes provide different events:

- `\Zikula\UsersModule\AccessEvents`
- `\Zikula\UsersModule\RegistrationEvents`
- `\Zikula\UsersModule\UserEvents`

There are MANY events throughout the UsersModule. These have been divided amongst three classes to better organize them.
Because there are so many, they are not documented here. Please see the respective classes for further information.

- Access Events: Events concerning login/logout/authentication
- Registration Events: Events concerning user registration
- User Events: Events concerning user account creation/display/manipulation.

In addition, Hooks are used extensively within the UsersModule. There are also 'hook-like' events that are used
within the UsersModule.
