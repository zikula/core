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
- [Authentication Methods](AuthenticationMethods.md)

### TBD

add more details about Users module

## Authentication

Separate modules like ZAuthModule are responsible for the *authentication* of each user. For further details refer to [Authentication](../Authentication/README.md).

### Security

With passwords, security should be an immediate concern of any site admin. The proper storage of passwords is a difficult
process and many systems have been provided to block attacks attempting to gain access to user data. The advantage of
the separation between UsersModule and authentication methods is that ALL of this can be provided by external systems
like Google or Facebook (via OAuth) and therefore relieve the Zikula site admin of the responsibility of password security.

### User deletion

A site admin always has the right to delete users from the UserAdministration page or via CLI. When deleting users, two
options are available: **full** deletion or **ghost** deletion.

 1) **Full deletion** means that the user record, all cascading records and all data in responding extensions are completely
    deleted. This could have unintended side effects and is not recommended. For example, the UID will be removed, thus
    if a third-party entity requires that user-record, the request could fail, creating 'orphaned' data.
 2) **Ghost deletion** means that the user's personal information (uname, email, etc) and all login ability is fully removed
    but the user record remains valid. This means that third-party records depending on the UserEntity (UID) will not
    fail and a 'ghost' user record will be returned.

In both cases, the username is added to the list of 'illegal' usernames for the future to prevent impersonating deleted users.
Additionally, the user cannot be reinstated.

## User profiles

TBD

## Importing users from a CSV file

- [Importing Users](ImportFromFile.md)

## Generate users

- [Generate users for test purposes](GenerateUsers.md)

## For developers

- [CurrentUserApi](Dev/CurrentUserApi.md)
- [Access, User and Registration events](Dev/AccessUserAndRegistrationEvents.md)
- [Profile interface](Dev/ProfileInterface.md)
- [Message interface](Dev/MessageInterface.md)
