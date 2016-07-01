Users and Authentication
========================

As of Core 1.4.3, the UsersModule has been entirely rewritten and has dramatic "under the hood" changes. Some of these
changes will be obvious to the site administrator because of the changes in the settings and admin interface. The user
interface is only slightly different and is most obvious in the login or registration processes.

Other documents in this folder will fully explain all the details. This document is meant to be an overview of the
changes and the rationale and methods now in use.

First, the UsersModule was simply too large and responsible for too many roles in the project. Because of this, these
roles have been split. Now, the UsersModule is only responsible for user management and maintenance of the single
entity definition of a 'user' within the Zikula ecosystem. The UserModule determines the status (active/pending/inactive)
of a user, various statistics like registration date and others, and a simple means of contacting users.

A new module, ZAuthModule is now responsible for the *authentication* of each user. This means that ZAuth is responsible
for authentication credentials: username, email and password, for users that are created *within zikula*. But users
are not required to be created within Zikula! (For more on this see the AuthenticationMethodInterface documentation). 
ZAuth therefore maintains the password and provides admin and user interfaces for the management of the credentials.

It should be noted that many of the settings and admin-interface controls have been relocated from Users to ZAuth. 

Authentication
--------------

It is important to consider what *authentication* means. Authentication is a method where a trusted relationship is 
established to indicate that a user *is who they say they are*. There are many methods to do this (OpenID, OAuth, etc.)
ZAuth is the same type of method, it is simply local instead of external. Typically, a user provides some kind of
credential (email, password, etc) and are provided then with an ID that has previously been created through some kind
of registration method.

Security
--------

With passwords, security should be an immediate concern of any site admin. The proper storage of passwords is a difficult
process and many systems have been provided to block attacks attempting to gain access to user data. The advantage of
the new UsersModule is that ALL of this can be provided by external systems like Google or Facebook (via OAuth) and
therefore relieve the Zikula site admin of the responsibility of password security.
