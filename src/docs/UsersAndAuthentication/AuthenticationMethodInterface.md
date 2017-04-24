AuthenticationMethodInterface
=============================

As of Zikula Core-1.4.3 a new method is introduced to provide a means for users to be authenticated for use in the
Zikula website. This method provides for two different types of authentication

 - Zikula\UsersModule\AuthenticationMethodInterface\ReEntrantAuthenticationMethodInterface
 - Zikula\UsersModule\AuthenticationMethodInterface\NonReEntrantAuthenticationMethodInterface

both methods extend a common interface

 - Zikula\UsersModule\AuthenticationMethodInterface\AuthenticationMethodInterface

ReEntrantAuthenticationMethodInterface
--------------------------------------

A ReEntrant method requires no local (e.g. as part of this website) data input. For example, Facebook or Google will ask
for credentials (if not already logged in), but these credentials are not entered 'locally' but rather at their website.
After authentication at their site, the user is 're-entered' back into the site where they requested the access.
When this is done, a 'token' is provided to the requesting website that indicates the user's status. This is then
used to provide access locally. The **OAuthModule** is an example of this type of method.

NonReEntrantAuthenticationMethodInterface
-----------------------------------------

A NonReEntrant method never leaves the local site to determine authentication and therefore typically requires a form
and the processing of that form. The method can maintain it's own table(s) and use this information to indicate a user's
status to the zikula website. The **ZAuthModule** is an example of this type of method. Registration forms MUST contain
an `email`, `uname` and `submit` field (may be hidden) and login forms must contain a `rememberme` field (boolean). 


Authentication, Login and Registration
--------------------------------------

As stated in the README.md file, Authentication is a method where a trusted relationship is established to indicate that
a user *is who they say they are*. It should be pointed out that this neither means 'login' nor 'registration' to a
website. An Authentication method is required to provide a means to authenticate. This will allow the Zikula website to
*login* that user based on the results of the authentication.

*Registration* is a more complicated process which also requires authentication, but additionally requires persisting
some data about the relationship between a Zikula UserEntity::Uid and the corresponding authentication data. This
process differs for all systems, but in each some relationship must be established. The custom AuthenticationMethod
module is required to manage this data and provide appropriate user/admin interfaces as needed for such management.


User Creation, modification and deletion
----------------------------------------

Because an AuthenticationMethod providing module is responsible for the initial authentication of a user, it is therefore
considered the user *creator*. The module therefore assumes responsibility for the modification of said user (for the
properties it manages, e.g. uname, email, password, etc).

However - it is *very important* to note that the providing module must rely on the UsersModule for deletion (via admin
interface) of said user. This is because UserModule will take appropriate steps to not orphan data and will fire the
required events, et al on user deletion. The providing authenticationMethod should react to said events in managing
their own data.


Hooks and Events in templates
-----------------------------

Authentication modules *must* implement hooks and events within their login and registration templates. See ZAuthModule
for examples:
  - src/system/ZAuthModule/Resources/views/Authentication/register.html.twig
  - src/system/ZAuthModule/Resources/views/Authentication/EmailLogin.html.twig
Also see:
  - \Zikula\UsersModule\Container\HookContainer
  - \Zikula\UsersModule\RegistrationEvents


Installation and discoverability
--------------------------------

In order for a method to be usable, it must be registered as a Dependency Injection service. The service must be 
*tagged* as `zikula.authentication_method` and include a unique alias as a service argument.

    <tag name="zikula.authentication_method" alias="my_authentication_method" />

This alias must match the alias defined in the Method class exactly.

After module installation, the method will appear in the Authentication Methods list in the UsersModule administration.
In order to be used, the method must be activated in this list.
