---
currentMenu: authentication
---
# User authentication

## Introduction

It is important to consider what *authentication* means. Authentication is a method where a trusted relationship is 
established to indicate that a user *is who they say they are*.

But users can be created within Zikula, but this is no requirement because there may be multiple and different authentiation methods!

## Existing authentication methods

### ZAuth

While the UsersModule is about the management of general user accounts, a separate module, ZAuthModule is responsible
for the *authentication* of each user. This means that ZAuth is responsible for authentication credentials: username,
email and password, for users that are created *within zikula*.

ZAuth therefore maintains the password and provides admin and user interfaces for the management of the credentials.  
In contrast to external authentication methods ZAuth does that simply local instead of using remote data.

### OAuth

The OAuthModule provides the ability to use OAuth for authentication with common services, like:

- Facebook
- GitHub
- Google
- Instagram
- LinkedIn

### Others

There are many other method possible to do authentication (e.g. LDAP, OpenID, etc.)

TBD

## For developers

- [AuthenticationMethodInterface](Dev/AuthenticationMethodInterface.md)
- [Authentication related events](Dev/AuthenticationRelatedEvents.md)
- [PasswordApi](Dev/PasswordApi.md)
