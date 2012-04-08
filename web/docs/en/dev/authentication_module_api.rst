__Authentication Module API__

PURPOSE
=======

The Authentication Module API allows the developer to implement alternative
authentication methods for the Zikula CMS through a custom-built Zikula module.

Zikula, as shipped, includes a standard authentication method via it's _Users_
system module. This module accepts an maintains a user name (and/or e-mail
address) and password, which are used to authenticate a user, granting access
to the Zikula-based web site (in other words, to _log in_). For many sites, a
user name (or e-mail address) and password managed by Zikula is sufficient for
providing user authentication during the login process.

In cases where the web site would like to expand its log-in options available
to its users, a custom module implementing the Authentication Module API can
be used. For example, a web site could offer its users the option to log in
with either their user name and password, or with their Google&trade; Account.
The custom module would implement the API, which would handle the
authentication process with Google, and (if successful) provide the Zikula
log-in process with the user identifier of the authenticated user.

There are several examples of alternate authentication methods and protocols:
OpenID, Facebook&trade; Connect, "Sign in with Twitter", etc. In addition to
these "standard" protocols, custom protocols are possible. For example, if
a web site has been re-implemented on the Zikula CMS platform, but the user
passwords in the previous system were encoded, then a custom authentication
module could be developed to authenticate the user against the previous
password database, and then transfer the authenticated password to the
Zikula system.

Several different authentication methods can be offered to the user
by implementing custom authentication modules for each and making them
available through either the standard login block and screen, or by
implementing custom templates for these elements.

THE ZIKULA LOG IN PROCESS
=========================

The user authentication--or log in--process for Zikula is fairly straight-
forward. It is controlled by the _Users_ system module, in addition to several
elements in the core CMS.

To initiate the process, authentication information is gathered from the user.
In the default process, this consists of a user name (or e-mail address, if
configured to accept these) and a password. For other authentication methods,
this information might consist of any combination of a user name, an e-mail
address, an authentication URL, a password, a pass phrase, a PIN (personal
identification number), or it could consist of nothing at all. An example of
a method not requiring any user authentication information is Google's
Federated Login, where Google takes care of asking for the appropriate
information. No matter what (if any) authentication information is gathered,
the authentication method is __always__ specified. The authentication method
indicates to Zikula which module should be used to authenticate the user with
the given information. If the user is given a choice, then that choice is
indicated; if no choice is made available, then the single option is indicated
behind the scenes.

Based on the specified authentication method, the gathered authentication
information is passed to the appropriate module implementing the Authentication
Module API. The module is responsible for interpreting the authentication
information, processing it appropriately, and if the user has provided
sufficient credentials to identify himself, return the user ID (`uid`) of the
Zikula Users module account that matches the authenticated user. Specifically,
the `authenticateUser(...)` method of the authentication module's `Api_Auth`
class is called.

It should be made clear that the `authenticateUser()` method __does not__ log
the user in to the Zikula-based site. Instead, it simply authenticates the user
based on the information provided, and either returns an authenticated `uid`,
or the boolean value `false` if the information provided fails to authenticate
to a valid user.

If the user-provided information authenticates to and returns a valid `uid`,
then the Zikula Users module and core elements work in concert to perform the
actual "log in" of the user.

In addition to the `authenticateUser()` method, the API defines several other
methods that must be implemented. Each is fully documented below. The
[OpenID module](http://code.zikula.org/openid) serves as an example
implementation of an authentication module. It provides authentication methods
for OpenID, Google Federated Login (including "regular" Google Accounts and
Google Apps Accounts), VeriSign&trade; SeatBelt, and several other providers
that offer some flavor of the OpenID protocol. This example module is fully
documented, and can be used as a template for creating your own custom
authentication module.

EXTERNAL AUTHENTICATION
=======================

Authentication methods that require the user to visit an external web site
to provide information and/or confirmation present a particular challenge
to the authentication process. The goal is to "exit" the site, "visit"
the authentication provider's site to complete the log in process, and then
"return" to the site, all while providing a relatively seamless and consistent
presentation to the user.

In order to support both authentication methods that require the user to visit
another site, and those that do not, the API defines two "flavors" of
authentication modules: _reentrant_ and _non-reentrant_.

Non-Reentrant Authentication
----------------------------

Non-reentrant authentication is the simplest implementation of the
Authentication Module API. Essentially, a non-reentrant implementation is
fully self-contained, and does not rely on any external __user-oriented__
process in order to authenticate the user. That is to say that the module
does not have to relinquish control to an external process at the user
interface level.

The _Users_ system module is an example of a non-reentrant authentication
module. Everything it needs to authenticate a user is available locally, or
is available through API calls. The user who is logging in does not have to
be directed to an external site in order to provide information or otherwise
confirm his desire to use his user name and password to log into a Zikula-based
site.

If a custom authentication method requires external data (either external to
Zikula but on the same server, or on another server entirely), but does not
need to direct the user interface to display an external web page, then it is
likely non-reentrant. In other words, if everything required to authenticate
the user can be done within the API (including access to other databases or
other servers), and without requiring user input, then it is non-reentrant.

Zikula identifies a non-reentrant authentication module by calling the
`isReentrant()` method of the module's `Api_Auth` class and receiving a
`false`.

Reentrant Authentication
------------------------

That leaves us with reentrant authentication methods. These methods require the
user to leave the Zikula-based site to provide some kind of information or
confirmation to an external authenticating system in order to complete the
process.

An example of a reentrant authentication module is the
[OpenID module](http://code.zikula.org/openid) available on the
[Cozi](http://code.zikula.org). When a user attempts to log in with an OpenID,
the module must pass the OpenID to the external system so that the user can
provide a password (or otherwise confirm that the Zikula-based site is
authorized to use the OpenID for the purposes of logging in). Once that
external step is completed, the external authentication system must reenter
the log in process on the Zikula-based site somehow without starting all over
again. This is done by giving the external system a URL with which to reenter
the process. The Zikula log in process can then pass the external system's
response to the request for authentication to the custom authentication module
(in this example, the OpenID module), and the log in can be completed. Zikula
identifies reentrant authentication modules by calling the `isReentrant()`
method of the authentication module's `Api_Auth` class, and receiving back a
value of `true`.

It is important that Zikula identify reentrant authentication processes.
Remember that an authentication module does not encompass the entire log in
process, but instead only implements the authentication part of the process.
The _Users_ system module is still in charge of the overall login process, and
therefore needs to know how to handle attempts by external systems to enter
the process mid-stream.

IMPLEMENTATION
==============

Requirements
------------

A custom authentication module must...

*   ...positively identify and authenticate a unique user of a Zikula-based
    web site. Conversely, an authentication module should not resolve
    authentication information to more than one account.

*   ...return `false` when an attempt to authenticate a user fails for any
    reason.

*   ...maintain its own mapping between its unique authentication information
    and a Zikula user ID (`uid`), and must return the appropriate `uid` if
    (and only if) a user successfully authenticates with the method it
    implements.

*   ...implement all required Api and Controller methods, as defined below.

*   ...fulfill all other requirements for a Zikula module.

A custom authentication module must __not__...

*   ...attempt to log a user into Zikula. It should only authenticate the user,
    and should allow Zikula to complete the rest of the log in process.

*   ...return a `uid` if a user is not positively identified, authenticated,
    and matched to a Zikula user account.

*   ...pass user-interface control to an external authenticating system unless
    it defines itself as reentrant, and appropriately handles data passed back
    to it from a returning external authenticating system.

Data Structures
---------------

*`$args['authentication_info']`

Many functions accept, as part of its `$args` parameter array, an `'authentication_info'`
data structure. This structure contains all of the information necessary to
identify and authenticate a user for the given authentication method. What is
contained in this data structure is defined entirely by the custom
authentication module. Since each custom authentication module has different
requirements, the contents cannot be described definitively here.

For example, the _Users_ system module (which implements the Authentication
Module API) requires that a user name or e-mail address, and a password be
provided as elements of an associative array (`'loginID'` or `'email'` and
`'pass'` array elements) called `'authentication_info'`. In addition, it requires that
a `'loginviaoption'` element be present, indicating whether a user name or an
e-mail address is expected.

The _OpenID_ module, as another example, requires a `'claimed_id'` array
element, containing the user-specified OpenID URL.

Other custom authentication modules may require similar data, completely
different data, or no data at all.

Additional information may be added to the `'authentication_info'` associative array
during the execution of the authentication process by the authentication
module, or the execution of the log in process by the _Users_ module, therefore
custom authentication modules should treat `'authentication_info'` as an associative array
and add any data it needs as elements of this array.

Api Classes and Methods
-----------------------

All classes and functions described are required, unless otherwise specified.

In all cases in this documentation, _ModName_ should be replaced with the
module name of the custom authentication module. For example, the OpenID
module is found in the `modules/OpenID` directory, and its classes are named
`OpenID_Api_Auth` (found in `modules/OpenID/lib/OpenID/Api/Auth.php`),
`OpenID_Api_Account`, `OpenID_Controller_Auth`, etc.

**ModName_Api_Auth Class**

The `ModName_Api_Auth` Class implements the functions necessary for Zikula to
request authentication of user information.

*`isReentrant()` Function*

Returns `false` if the authentication method implemented by the module is not
reentrant (does not pass control of the user-interface to an external
authenticating system). Returns `true` if the method is reentrant, requiring
that user-interface control be passed to an external system.

If the authentication method is reentrant, then additional requirements are
imposed on the module implementation, which are described below.

*`authenticateUser(...)` Function*

Perform all steps necessary to uniquely identify and authenticate a user based
on the information passed to it in the `$args['authentication_info']` data structure. If
the user information results in a positively identified and authenticated user
mapped to a Zikula `uid`, then return that `uid`; otherwise return `false`.
This is the primary function called by Zikula during the log in process. If the
authentication method is reentrant and requires that the user interface be
redirected to an external authenticating system, then it happens within this
function (or within a function called by this function). In those cases, this
function __must be reentrant__, and __must__ process data returned to it by
the external authentication system without restarting the authentication
process anew.

*`checkPassword(...)` Function*

Similar to the `authenticateUser(...)` function, perform all steps necessary
to uniquely identify and authenticate a user based on the information passed
to it in the `$args['authentication_info']` data structure. Instead of returning a `uid`,
however, simply return `true` if the user is positively authenticated. Return
`false` if the user information cannot be authenticated. This allows Zikula
to authenticate user identifying information without any attempt to log a user
into the system. This is useful, for example, when one-time authorizations
are required apart from the log in process (e.g., validate a user name and
password prior to completing a security-sensitive operation).

If appropriate, the `authenticateUser(...)` function may call this function to
complete the authentication portion of its process. If this design choice is
made, and the method is reentrant, then the call to the external system likely
happens within this function (or some function called by it), and likewise
this function __must__ be reentrant.

*`getUidForAuthenticationInfo(...)` Function*

Given the `'authentication_info'` gathered from the user, return the `uid` mapped to that
authentication information. This function allows a `uid` to be retrieved
without going through the full authentication process. This function should
only be called after a successful call to `authenticateUser(...)`, as data
returned by an external authentication process may be required in order to
complete the mapping to a `uid`.

**ModName_Api_Account Class**

As with other Zikula modules, this class implements functions defined by the
Zikula Account API.

*`getAll()` Function (__optional, but recommended__)*

Returns an array of items to display on the user's account panel, providing
access to module specific user account maintenance function. In the context of
a custom authentication module, this would likely consist of links to one or
more functions allowing the user to associate his account with an identifier,
disassociate his account with that identifier, and/or view the details of his
account's association with the authentication method.

The _Users_ system module, while providing links to account-related functions,
does not provide links specifically for authentication-related functions.

The _OpenID_ module provides a link that allows a user to add an OpenID
association to his account, or to remove an existing association from his
account.

**ModName_Api_User and/or ModName_Api_Admin and other Classes**

The Authentication Module API does not specify any required functions for
either the User or Admin API classes. Module-specific functions, however, will
likely be implemented in at least a `ModName_Api_User` class, if not both
classes.

Other API classes may be defined by the module as the developer sees fit.

Controller Classes and Functions
--------------------------------

**ModName_Controller_Auth Class**

The _Users_ system module's log in screen and log in block have been designed
to detect and display additional authentication methods as options, and to
pass the `'authentication_info'` appropriate for the chosen authentication method to the
proper authentication module.  Use of this facility is optional, and if not
used can be replaced by custom templates put in place by the site's
administrator.

In order to use this facility, certain functions must be defined.

*`loginBlockFields()` Function*

(_Optional and ignored if the multiple authentication log in block facility is
not used, required if this facility is used._) Return the fields to display
to gather the authentication information (the `'authentication_info'`) entered by the
user for this authentication method on the _users_ module's log in block. The
return value should be a rendered template of form fields compatible with the
_Users_ module's log in block (or the equivalent of a rendered template).

*`loginBlockIcon()` Function*

(_Optional and ignored if the multiple authentication log in block facility is
not used, required if this facility is used._) Return one or more icons to
display as part of a list or collection of icons that the user clicks on in
the _Users_ module's log in block to choose this authentication method. The
return value should be a rendered template of icons with links to appropriate
functions (or the equivalent of a rendered template).

*`loginFormFields()` Function*

(_Optional and ignored if the multiple authentication log in form facility is
not used, required if this facility is used._) Return the fields to display
to gather the authentication information (the `'authentication_info'`) entered by the
user for this authentication method on the _Users_ module log in form. The
return value should be a rendered template of form fields compatible with the
_Users_ module's log in form (or the equivalent of a rendered template).

*`loginFormIcon()` Function*

(_Optional and ignored if the multiple authentication log in form facility is
not used, required if this facility is used._) Return one or more icons to
display as part of a list or collection of icons that the user clicks on in
the _Users_ module's log in form to choose this authentication method. The
return value should be a rendered template of icons with links to appropriate
functions (or the equivalent of a rendered template).

**ModName_Controller_User and/or ModName_Controller_Admin and other Classes**

Optional, and as appropriate for functions provided to the user (through the
user account panel or through other means) and/or the administrator (through
the administration panel or through other means).

Other classes may be implemented as the developer's discretion, consistent with
general Zikula module requirements.

Other Types of Classes and Functions
------------------------------------

Other classes and functions can be implemented at the discretion of the
module developer and consistent with Zikula module requirements.

One common example of classes and functions that are commonly required for
this type of module are those that implement database tables an access to the
data they contain in order to store and maintain a mapping between
authentication identifiers and Zikula `uid`s.

The _OpenID_ module, as an example, defines several classes and functions using
_Doctrine_ in order to define and maintain the tables needed to manage OpenID
associations to `uid`s, and for other data required to interact with OpenID
providers.  In addition, the _OpenID_ module defines a few "helper" classes
to implement specific functions for certain OpenID providers.

Other custom authentication modules might define their own classes, implement
vendor classes imported from other projects, etc.

TO-DO
=====

*   The current API requires that a user account (a record in the Users module)
    exist for the user. The only way to create this record, at the moment, is
    through the registration process in the _Users_ system module. This process
    requires that a web site user name and password be established.

    For sites that would like to completely replace the user name and password
    login option with an alternate authentication method, the registration
    process should not require a password. If a password were not required,
    then an extension to the Authentication Module API could be added that
    would, for example, allow a user to register as a new user using
    information obtained from his Google Account through Google's OAuth
    protocol.

*   The current API does not define an AJAX component. Authentication methods
    that require the user to visit another site to complete the process would
    probably benefit from the use of AJAX. Some of these methods make
    authentication available through AJAX-enabled "pop-up" boxes.  The API
    should be extended to support these processes.

"BUYER BEWARE"
==============

A final word of caution: obtain custom authentication modules only from
trusted sources, and inspect the code of any authentication module (no matter
if the source is trusted or not) closely. Granting a third party control and
access to your authentication (log in) process and your users' data should be
done with care.