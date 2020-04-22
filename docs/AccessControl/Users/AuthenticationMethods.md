---
currentMenu: users
---
# Authentication Methods

In the Users module, the admin can select the _authentication method(s)_ to which the admin would like to allow their
users access. Simply check the box next to the methods to make them available to users. It is not recommended to provide
_many_ options to users. While theoretically allowing many is possible, it is not good design for a site and could
confuse your users, causing them to register multiple accounts with different services. In addition, if an email address
is registered with one authentication method, the same email address cannot register with a different authentication
method (this is a change in Core-3.0). 

Zikula comes standard with the ZikulaZAuthModule which provides three methods:

### NATIVE_UNAME (default)

This method requires that the `uname` (user name) selected by the registering user be **unique**. However, the `email`
address input by the registering user is **NOT** required to be unique when compared to other users registering with
ZAuth. When logging in, users are presented with a form requiring their **uname** and password.

### NATIVE_EMAIL

This method requires that **both** the `uname` and `email` values be unique when compared to other users registering with
ZAuth. When logging in, users are presented with a form requiring their **email** and password.

### NATIVE_EITHER

This method requires that both the `uname` and `email` values be unique when compared to other users registering with
ZAuth. When logging in, users are presented with a form requiring either their **uname** or **email**
and password. The login form detects the type of value in the first field and authenticates as needed from that info.

#### Password security note

Core-3.0 introduces a new and very much improved security layer for user passwords. Relying on Symfony and the password
encryption improvements made in PHP 7, Zikula now automatically upgrades users to the best encryption available on the
server. This is checked and improved on _every_ login.

## Additional Methods

Additional methods may be listed if other extensions supporting AuthenticationMethodInterface are installed
and configured properly. Zikula provides the ZikulaOAuthModule which implements authentication via Facebook, Github,
Google, LinkedIn or Instagram. Other third party extensions may provide more. For developer information on creating a
new authentication method, see [AuthenticationMethodInterface.md](../Authentication/Dev/AuthenticationMethodInterface.md)

