---
currentMenu: users
---
# User accounts

## Users and user management

In old Zikula versions the UsersModule was very large and responsible for too many roles in the project.
Because of this, these roles have been split. Now, the UsersModule is only responsible for user management
and maintenance of the single entity definition of a 'user' within the Zikula ecosystem. The UserModule
determines the status (active/pending/inactive) of a user, various statistics like registration date and others,
and a simple means of contacting users.

### User types

Zikula will consider all users to be within two categories: "logged in" and "not logged in" users. Logged in users can
be granted additional rights and roles with the Permissions Module. All users utilize sessions. It is not possible to 
visit any zikula page without beginning a session. Users that are not logged in are all considered UserId = 1. Other
users are assigned a UID as they are created.

### Settings

- [Users configuration](UsersConfiguration.md)

### TBD

add more details about Users module

## Authentication

A separate module, ZAuthModule is responsible for the *authentication* of each user. For further details refer to [Authentication](Authentication.md).

### Security

With passwords, security should be an immediate concern of any site admin. The proper storage of passwords is a difficult
process and many systems have been provided to block attacks attempting to gain access to user data. The advantage of
the separation between UsersModule and authentication methods is that ALL of this can be provided by external systems
like Google or Facebook (via OAuth) and therefore relieve the Zikula site admin of the responsibility of password security.

## User profiles

TBD

## For developers

- [CurrentUserApi](Dev/CurrentUserApi.md)
- [Access, User and Registration events](Dev/AccessUserAndRegistrationEvents.md)
- [Profile interface](Dev/ProfileInterface.md)
- [Message interface](Dev/MessageInterface.md)
