Events
======

CORE
----

#### `api.method_not_found`
Called in instances of Zikula_AbstractApi from __call()
Receives arguments from __call($method, argument) as $args
$event['method'] is the method which didn't exist in the main class.
$event['args'] is the arguments that were passed.
The event subject is the class where the method was not found.
Must exit if $event['method'] does not match whatever the handler expects.
Modify $event->data and $event->stopPropagation().

#### `core.preinit`
Occurs after the config.php is loaded.

#### `core.init`
Occurs after each `System::init()` stage, `$event['stage']` contains the stage.
To check if the handler should execute, do `if($event['stage'] & Zikula_Core::STAGE_*)`

#### `core.postinit`
Occurs just before System::init() exits from normal execution.

#### `controller.method_not_found`
Called in instances of `Zikula_AbstractController` from `__call()`
Receives arguments from `__call($method, argument)` as `$args`
`$event['method']` is the method which didn't exist in the main class.
`$event['args']` is the arguments that were passed.
The event subject is the class where the method was not found.
Must exit if `$event['method']` does not match whatever the handler expects.
Modify `$event->data` and `$event->stopPropagation()`

#### `dbobject.pre/post*`
Takes subject of $this.

MODULE
------

#### `installer.module.installed`
Called after a module is successfully installed.
Receives `$modinfo` as args

#### `installer.module.upgraded`
Called after a module is successfully upgraded.
Receives `$modinfo` as args

#### `installer.module.activated`
Called after a module is successfully activated.
Receives `$modinfo` as args

#### `installer.module.deactivated`
Called after a module is successfully deactivated.
Receives `$modinfo` as args

#### `installer.module.uninstalled`
Called after a module is successfully uninstalled.
Receives `$modinfo` as args

#### `installer.subscriberarea.uninstalled`
Called after a hook subscriber area is unregistered.
Receives args['areaid'] as the areaId.  Use this to remove orphan data associated with this area.


#### `module_dispatch.postloadgeneric`
receives the args `['modinfo' => $modinfo, 'type' => $type, 'force' => $force, 'api' => $api]`

#### `module_dispatch.preexecute`
Occurs in `ModUtil::exec()` before function call with the following args:
`['modname' => $modname, 'modfunc' => $modfunc, 'args' => $args, 'modinfo' => $modinfo, 'type' => $type, 'api' => $api]`

#### `module_dispatch.postexecute`
Occurs in `ModUtil::exec()` after function call with the following args:
`['modname' => $modname, 'modfunc' => $modfunc, 'args' => $args, 'modinfo' => $modinfo, 'type' => $type, 'api' => $api]`
receives the modules output with `$event->getData();`
can modify this output with `$event->setData($data);`

#### `module_dispatch.type_not_found`
if `$type` is not found in `ModUtil::exec()` (e.g. no admin.php)
_This is for classic module types only._
`['modname' => $modname, 'modfunc' => $modfunc, 'args' => $args, 'modinfo' => $modinfo, 'type' => $type, 'api' => $api]`
This kind of eventhandler should

1. Check $event['modfunc'] to see if it should run else exit silently.
2. Do something like $result = {$event['modfunc']}({$event['args'});
3. Save the result $event->setData($result).
4. $event->stopPropagation().
5. return void

#### `module_dispatch.custom_classname`
In order to override the classname calculated in `ModUtil::exec()`
In order to override a pre-existing controller/api method, use this event type to override the class name that is loaded.
This allows to override the methods using inheritance.
Receives no subject, args of `['modname' => $modname, 'modinfo' => $modinfo, 'type' => $type, 'api' => $api]`
and 'event data' of `$className`.  This can be altered by setting `$event->setData()` followed by `$event->stopPropagation()`

#### `module_dispatch.service_links`
Occurs when building admin menu items. Adds sublinks to a Services menu that is appended to all modules if populated.
triggered by module_dispatch.postexecute in bootstrap.
format data like so:

    $event->data[] = ['url' => ModUtil::url(<modname>, <type>, <method>), 'text' => __('Link Text')];

#### `module.mailer.api.sendmessage`
Invoked from `Mailer_Api_User#sendmessage`. Subject is `Mailer_Api_User` with `$args`.
This is a notifyUntil event so the event must `$event->stopPropagation()` and set any
return data into `$event->data`, or `$event->setData()`

#### `pageutil.addvar_filter`
Used to override things like system or module stylesheets or javascript.
Subject is the `$varname`, and `$event->data` an array of values to be modified by the filter.
Simply test with something like

    if (($key = array_search('system/Users/javascript/somescript.js', $event->data)) !== false) {
        $event->data[$key] = 'config/javascript/myoverride.js';
    }

This single filter can be used to override all css or js scripts or any other var types
sent to `PageUtil::addVar()`.


ERRORS
------
#### `system.outputfilter`
Filter type event for output filter HTML sanitisation

### THEME AND VIEW

#### `theme.ajax_request`
Triggered by a native ajax request from a theme.  This occurs when the following call is made
`ajax.php?module=theme&func=dispatch&.....`
Handlers should be registered in the theme.

#### `theme.preinit`
Occurs on the startup of the `Zikula_View_Theme#__construct()`.
The subject is the Zikula_View_Theme instance.
Is useful to setup a customized theme configuration or cache_id.

#### `theme.init`
Occurs just before `Zikula_View_Theme#__construct()` finishes.
The subject is the Zikula_View_Theme instance.

#### `theme.load_config`
Runs just before `Theme#load_config()` completed.  Subject is the Theme instance.

#### `theme.prefetch`
Occurs in `Theme::themefooter()` just before getting the `$maincontent`.  The
event subject is `$this` (Theme instance) and has $maincontent as the event data
which you can modify with `$event->setData()` in the event handler.

#### `theme.postfetch`
Occurs in `Theme::themefooter()` just after rendering the theme.  The
event subject is `$this` (Theme instance) and the event data is the rendered
output which you can modify with `$event->setData()` in the event handler.

#### `view.init`
Occurs just before `Zikula_View#__construct()` finishes.
The subject is the Zikula_View instance.

#### `view.postfetch`
Filter of result of a fetch.  Receives `Zikula_View` instance as subject, args are
`['template' => $template], $data was the result of the fetch to be filtered.`


USER ACCOUNTS, REGISTRATIONS, AND LOG-INS
-----------------------------------------

#### `module.users.ui.display_view`
A hook-like UI event that is triggered when a user's account detail is viewed. This allows another module
to intercept the display of the user account detail in order to add its own information.

To add display elements to the user account detail, render output and add this as an element in the event's
data array.

 * The subject contains the user's account record.
 * The `'id'` argument contain's the user's uid.

#### `module.users.ui.login.started`
Occurs at the beginning of the log-in process, before the registration form is displayed to the user.

NOTE: This event will not fire if the log-in process is entered through any other method other than visiting the
log-in screen directly. For example, if automatic log-in is enabled following registration, then this event
will not fire when the system passes control from the registration process to the log-in process.

Likewise, this event will not fire if a user begins the log-in process from the log-in block or a log-in
plugin if the user provides valid authentication information. This event will fire, however, if invalid
information is provided to the log-in block or log-in plugin, resulting in the user being
redirected to the full log-in screen for corrections.

This event does not have any subject, arguments, or data.

#### `module.users.ui.form_edit.login_block`
A hook-like UI event that is triggered when the login block is displayed. This allows another module to 
intercept the display of the login form on the block to add its own form elements for submission.

To add elements to the form, render the output and add this as an array element to the event's
data array.

This event does not have any subject, arguments, or data.

#### `module.users.ui.form_edit.login_screen`
A hook-like UI event that is triggered when the login screen is displayed. This allows another module to 
intercept the display of the full-page version of the login form to add its own form elements for submission.

To add elements to the form, render the output and add this as an array element to the event's
data array.

This event does not have any subject, arguments, or data.

#### `user.login.veto`
Occurs immediately prior to a log-in that is expected to succeed. (All prerequisites for a
successful login have been checked and are satisfied.) This event allows a module to
intercept the login process and prevent a successful login from taking place.

This event uses `notify()`, so handlers are called until either one vetoes the login attempt,
or there are no more handlers for the event. A handler that needs to veto a login attempt
should call `stopPropagation()`. This will prevent other handlers from receiving the event, will
return to the login process, and will prevent the login from taking place. A handler that
vetoes a login attempt should set an appropriate error message and give any additional
feedback to the user attempting to log in that might be appropriate. If a handler does not
need to veto the login attempt, then it should simply return null (`return;` with no
return value).

Note: the user __will not__ be logged in at the point where the event handler is
executing. Any attempt to check a user's permissions, his logged-in status, or any
operation will return a value equivalent to what an anonymous (guest) user would see. Care
should be taken to ensure that sensitive operations done within a handler for this event
do not introduce breaches of security.

 * The subject of the event will contain the user's account record, equivalent to
   `UserUtil::getVars($uid)`.
 * The arguments of the event are:
    * `'authentication_method'` will contain the name of the module and the name of the method that was used to authenticated the user.
    * `'uid'` will contain the user's uid.

An event handler can prevent (veto) the log-in attempt by calling `stopPropagation()` on the event. This is
enough to ensure that the log-in attempt is stopped, however this will result in a `Zikula_Exception_Forbidden`
exception being thrown.

To, instead, redirect the user back to the log-in screen (after possibly setting an error message that will
be displayed), then set the event data to contain an array with a single element, `retry`, having a value
of true (e.g., `$event->setData(['retry' => true]);`).  This will signal the log-in process to go back
to the log-in screen for another attempt. The expectation is that the notifying event handler has set an
error message, and that the user will be able to log-in if the instructions in that message are followed,
or the conditions in that message can be met.

The Legal module uses this method when vetoing an attempt, if the Legal module has established a hook with the
log-in screen. The user is redirected back to the log-in screen and now that the user is known, the
Legal module is able to display a form fragment directly on the log-in screen which allows the user
to accept the policies that remain unaccepted. Assuming that the user accepts the policies, his
next attempt at logging in will be successful because the condition in the Legal module that caused the
veto no longer exists.

Another alternative is to "break into" the log-in process to redirect the user to a form (or something
similar) that allows him to correct whatever situation is causing his log-in attempt to be vetoed. The
expectation is that the notifying event handler will direct the user to a form to correct the situation,
and then __redirect the user back into the log-in process to re-attempt logging in__. To accomplish this,
instead of setting the `'retry'` event data, the notifying handler should set the `'redirect_func'`
event data structure. This is an array which defines the information necessary to direct the
user to a controller function somewhere in the Zikula system (likely, within the same module as that
which is vetoing the attempt). This array contains the following:

 * `'modname'` The name of the module where the controller function is defined.
 * `'type'` The library type that defines the function.
 * `'func'` The name of the function itself.
 * `'args'` An array of function argument key-value pairs to pass to the function when calling it. Since the function
            will be called through a redirect, any parameters will be converted to GET parameters on the URL, so
            the developer should consider the minimum set to include--preferably none. Session variables are an
            alternative to passing function arguments.

In addition, if information from the log-in attempt is needed within the function, it can be made available in
session variables. To do this, add an array called `'session'` to the `'redirect_func'` array structure. The contents
of the `'session'` array must be:

 * `'namespace'` The session name space in which to store the variable.
 * `'var'` The name of the session variable.

An array will be stored in that variable, containing information from the log-in process. The elements of this array will
be:

 * `'returnurl'` The URL where the user should be redirected upon successfully logging in.
 * `'authentication_info'` An array containing the authentication information entered by the user. The contents
                           of this array depends entirely on the authentication method.
 * `'authentication_method'` An array containing the `'modname'` (module name) of the authentication module, and
                             the `'method'` name of the authentication method being used by the user who is logging in.
 * `'rememberme'` A flag indicating whether the user checked the box to remain logged in.
 * `'user_obj'` The user object array (same as received when calling `UserUtil::getVars($uid);`) of the user who is
                logging in.

This information is also passed back to the log-in process when the user is redirected back there.

The Users module uses this method to handle users who have been forced by the administrator to change their password
prior to logging in. The code used for the notification might look like the following example:

    $event->stopPropagation();
    $event->setData([
        'redirect_func'  => [
            'modname'   => 'ZikulaUsersModule',
            'type'      => 'user',
            'func'      => 'changePassword',
            'args'      => [
                'login'     => true
            ],
            'session'   => [
                'var'       => 'Users_Controller_User_changePassword',
                'namespace' => 'Zikula_Users'
            ]
        ]
    ]);

    LogUtil::registerError(__("Your log-in request was not completed. You must change your web site account's password first."));

In this example, the user will be redirected to the URL pointing to the `changePassword` function. This URL is constructed by calling
`ModUtil::url()` with the modname, type, func, and args specified in the above array. The `changePassword` function also needs access
to the information from the log-in attempt, which will be stored in the session variable and namespace specified. This is accomplished
by calling `SessionUtil::setVar()` prior to the redirect, as follows:

    SessionUtil::setVar('Users_Controller_User_changePassword', $sessionVars, 'Zikula_Users' true, true);

where `$sessionVars` contains the information discussed previously.

#### `module.users.ui.login.succeeded`
Occurs right after a successful attempt to log in, and just prior to redirecting the user to the desired page.
All handlers are notified.

 * The event subject contains the user's user record (from `UserUtil::getVars($event['uid'])`)
 * The arguments of the event are as follows:
    * `'authentication_module'` an array containing the authenticating module name (`'modname'`) and method (`'method'`)
        used to log the user in.
    * `'redirecturl'` will contain the value of the 'returnurl' parameter, if one was supplied, or an empty
        string. This can be modified to change where the user is redirected following the login.

__The `'redirecturl'` argument__ controls where the user will be directed at the end of the log-in process.
Initially, it will be the value of the returnurl parameter provided to the log-in process, or blank if none was provided.

The action following login depends on whether WCAG compliant log-in is enabled in the Users module or not. If it is enabled,
then the user is redirected to the returnurl immediately. If not, then the user is first displayed a log-in landing page,
and then meta refresh is used to redirect the user to the returnurl.

If a `'redirecturl'` is specified by any entity intercepting and processing the `user.login.succeeded` event, then
the URL provided replaces the one provided by the returnurl parameter to the login process. If it is set to an empty
string, then the user is redirected to the site's home page. An event handler should carefully consider whether
changing the `'redirecturl'` argument is appropriate. First, the user may be expecting to return to the page where
he was when he initiated the log-in process. Being redirected to a different page might be disorienting to the user.
Second, all event handlers are being notified of this event. This is not a `notify()` event. An event handler
that was notified prior to the current handler may already have changed the `'redirecturl'`.

Finally, this event only fires in the event of a "normal" UI-oriented log-in attempt. A module attempting to log in
programmatically by directly calling the core functions will not see this event fired.

#### `module.users.ui.login.failed`
Occurs right after an unsuccessful attempt to log in. All handlers are notified.

 * The event subject contains the user's user record (from `UserUtil::getVars($event['uid'])`) if it has been found, otherwise null
 * The arguments of the event are as follows:
    * `'authentication_module'` an array containing the authenticating module name (`'modname'`) and method (`'method'`)
        used to log the user in.
    * `'authentication_info'` an array containing the authentication information entered by the user (contents will vary by method).
    * `'redirecturl'` will initially contain an empty string. This can be modified to change where the user is redirected following the failed login.

__The `'redirecturl'` argument__ controls where the user will be directed following a failed log-in attempt.
Initially, it will be an empty string, indicating that the user should continue with the log-in process and be presented
with the log-in form.

If a `'redirecturl'` is specified by any entity intercepting and processing the `user.login.failed` event, then
the user will be redirected to the URL provided, instead of being presented with the log-in form.  An event handler
should carefully consider whether changing the `'redirecturl'` argument is appropriate. First, the user may be expecting
to return to the log-in screen . Being redirected to a different page might be disorienting to the user.
Second, all event handlers are being notified of this event. This is not a `notify()` event. An event handler
that was notified prior to the current handler may already have changed the `'redirecturl'`.

Finally, this event only fires in the event of a "normal" UI-oriented log-in attempt. A module attempting to log in
programmatically by directly calling core functions will not see this event fired.

#### `module.users.ui.logout.succeeded`
Occurs right after a successful logout. All handlers are notified.

 * The event's subject contains the user's user record
 * Args contain array of `['authentication_method' => $authenticationMethod,
                           'uid'                   => $uid];`

#### `user.gettheme`
Called during UserUtil::getTheme() and is used to filter the results.  Receives arg['type']
with the type of result to be filtered and the $themeName in the $event->data which can
be modified.  Must $event->stopPropagation() if handler performs filter.

#### `user.account.create`
Occurs after a user account is created. All handlers are notified. It does not apply to creation of a pending
registration. The full user record created is available as the subject. This is a storage-level event,
not a UI event. It should not be used for UI-level actions such as redirects.

 * The subject of the event is set to the user record that was created.

#### `module.users.ui.form_edit.new_user`
A hook-like event triggered when the adminitstrator's new user form is displayed, which allows other 
modules to intercept and display their own elements for submission on the new user form.

To add elements to the new user form, render output and add this as an array element on the event's
data array.

There is no subject and no arguments for the event.

#### `module.users.ui.form_edit.modify_user`
A hook-like event triggered when the modify user form is displayed, which allows other 
modules to intercept and display their own elements for submission on the new user form.

To add elements to the modify user form, render output and add this as an array element on the event's
data array.

 * The subject contains the current state of the user object, possibly edited from its original state.
 * The `'id'` argument contains the uid of the user account.

#### `user.account.update`
Occurs after a user is updated. All handlers are notified. The full updated user record is available
as the subject. This is a storage-level event, not a UI event. It should not be used for UI-level
actions such as redirects.

 * The subject of the event is set to the user record, with the updated values.

#### `module.users.ui.form_delete`
A hook-like event that is triggered when the delete confirmation form is displayed. It allows other modules
to intercept and add to the delete confirmation form.

 * The subject of the event is not set.
 * The the argument `'id'` is the uid of the user who will be deleted if confirmed.

#### `module.users.ui.validate_delete`
A hook-like event that is triggered when the delete confirmation form is submitted and the submitted data
is being validated prior to processing. It allows other modules to intercept and add to the delete confirmation 
form, and in this case to validate the data entered on the portion of the delete confirmation form that
they injected with the corresponding `form_delete` event.

 * The subject of the event is not set.
 * The the argument `'id'` is the uid of the user who will be deleted if confirmed.

#### `module.users.ui.process_delete`
A hook-like event that is triggered when the delete confirmation form is submitted and the submitted data
is has validated. It allows other modules to intercept and add to the delete confirmation 
form, and in this case to process the data entered on the portion of the delete confirmation form that
they injected with the corresponding `form_delete` event. This event will be triggered after the 
`user.account.delete` event.

 * The subject of the event is not set.
 * The the argument `'id'` is the uid of the user who will be deleted if confirmed.

#### `user.account.delete`
Occurs after a user is deleted from the system. All handlers are notified. The full user record
deleted is available as the subject. This is a storage-level event, not a UI event. It should not be
used for UI-level actions such as redirects.

 * The subject of the event is set to the user record that is being deleted.

#### `module.users.ui.registration.started`
Occurs at the beginning of the registration process, before the registration form is displayed to the user.

#### `module.users.ui.form_edit.new_registration`
A hook-like event triggered when the registration form is displayed, which allows other modules to intercept
and display their own elements for submission on the registration form.

To add elements to the registration form, render output and add this as an array element on the event's
data array.

There is no subject and no arguments for the event.

#### `module.users.ui.form_edit.modify_registration`
A hook-like event triggered when the administrator's modify registration form is displayed, which allows other 
modules to intercept and display their own elements for submission on the new user form.

To add elements to the modify registration form, render output and add this as an array element on the event's
data array.

 * The subject contains the current state of the registration object, possibly edited from its original state.
 * The `'id'` argument contains the uid of the registration record.

#### `module.users.ui.registration.succeeded`
Occurs after a user has successfully registered a new account in the system. It will follow either a `registration.create`
event, or a `user.create` event, depending on the result of the registration process, the information provided by the user,
and several configuration options set in the Users module. The resultant record might
be a fully activated user record, or it might be a registration record pending approval, e-mail verification,
or both.

If the registration record is a fully activated user, and the Users module is configured for automatic log-in,
then the system's next step (without any interaction from the user) will be the log-in process. All the customary
events that might fire during the log-in process could be fired at this point, including (but not limited to)
 `user.login.veto` (which might result in the user having to perform some action in order to proceed with the
log-in process), `user.login.succeeded`, and/or `user.login.failed`.

 * The event's subject is set to the registration record (which might be a full user record).
 * The event's arguments are as follows:
    * `'returnurl'` A URL to which the user is redirected at the very end of the registration process.

__The `'redirecturl'` argument__ controls where the user will be directed at the end of the registration process.
Initially, it will be blank, indicating that the default action should be taken. The default action depends on two
things: first, whether the result of the registration process is a registration request record or is a full user record,
and second, if the record is a full user record then whether automatic log-in is enabled or not.

If the result of the registration process is a registration request record, then the default action is to direct the
user to a status display screen that informs him that the registration process has been completed, and also tells
him what next steps are required in order to convert that request into a full user record. (The steps to be
taken may be out of the user's control--for example, the administrator must approve the request. The steps to
be taken might be within the user's control--for example, the user must verify his e-mail address. The steps might
be some combination of both within and outside the user's control.

If the result of the registration process is a full user record, then one of two actions will happen by default. Either
the user will be directed to the log-in screen, or the user will be automatically logged in. Which of these two occurs
is dependent on a module variable setting in the Users module. During the login process, one or more additional events may
fire.

If a `'redirecturl'` is specified by any entity intercepting and processing the `user.registration.succeeded` event, then
how that redirect URL is handled depends on whether the registration process produced a registration request or a full user
account record, and if a full user account record was produced then it depends on whether automatic log-in is enabled or
not.

If the result of the registration process is a registration request record, then by specifying a redirect URL on the event
the default action will be overridden, and the user will be redirected to the specified URL at the end of the process.

If the result of the registration process is a full user account record and automatic log-in is disabled, then by specifying
a redirect URL on the event the default action will be overridden, and the user will be redirected to the specified URL at
the end of the process.

If the result of the registration process is a full user account record and automatic log-in is enabled, then the user is
directed automatically into the log-in process. A redirect URL specified on the event will be passed to the log-in process
as the default redirect URL to be used at the end of the log-in process. Note that the user has NOT been automatically
redirected to the URL specified on the event. Also note that the log-in process issues its own events, and any one of them
could direct the user away from the log-in process and ultimately from the URL specified in this event. Note especially that
the log-in process issues its own `module.users.ui.login.succeeded` event that includes the opportunity to set a redirect URL.
The URL specified on this event, as mentioned previously, is passed to the log-in process as the default redirect URL, and
therefore is offered on the `module.users.ui.login.succeeded` event as the default. Any handler of that event, however, has
the opportunity to change the redirect URL offered. A `module.users.ui.registration.succeeded` handler can reliably predict
whether the user will be directed into the log-in process automatically by inspecting the Users module variable
`Users_Constant::MODVAR_REGISTRATION_AUTO_LOGIN` (which evaluates to `'reg_autologin'`), and by inspecting the `'activated'`
status of the registration or user object received.

An event handler should carefully consider whether changing the `'redirecturl'` argument is appropriate. First, the user may
be expecting to return to the log-in screen . Being redirected to a different page might be disorienting to the user. Second,
all event handlers are being notified of this event. This is not a `notify()` event. An event handler that was notified
prior to the current handler may already have changed the `'redirecturl'`.

#### `module.users.ui.registration.failed`
Occurs after a user attempts to submit a registration request, but the request is not saved successfully.
The next step for the user is a page that displays the status, including any possible error messages.
 * The event subject contains null
 * The arguments of the event are as follows:
    * `'redirecturl'` will initially contain an empty string. This can be modified to change where the user is redirected following the failed login.

__The `'redirecturl'` argument__ controls where the user will be directed following a failed log-in attempt.
Initially, it will be an empty string, indicating that the user will be redirected to a page that displays status and error information.

If a `'redirecturl'` is specified by any entity intercepting and processing the `user.login.failed` event, then
the user will be redirected to the URL provided, instead of being redirected to the status/error display page.
An event handler should carefully consider whether changing the `'redirecturl'` argument is appropriate. First, the
user may be expecting to be directed to a page containing information on why the registration failed. Being redirected to a different
page might be disorienting to the user. Second, all event handlers are being notified of this event. This is not a
`notify()` event. An event handler that was notified prior to the current handler may already have changed the `'redirecturl'`.

#### `user.registration.create`
Occurs after a registration record is created, either through the normal user registration process, or through the
administration panel for the Users module. This event will not fire if the result of the registration process is a
full user record. Instead, a user.account.create event will fire.
This is a storage-level event, not a UI event. It should not be used for UI-level actions such as redirects.

 * The subject of the event is set to the registration record that was created.

#### `user.registration.update`
Occurs after a registration record is updated (likely through the admin panel, but not guaranteed).
This is a storage-level event, not a UI event. It should not be used for UI-level actions such as redirects.

 * The subject of the event is set to the registration record, with the updated values.

#### `user.registration.delete`
Occurs after a registration record is deleted. This could occur as a result of the administrator deleting the record
through the approval/denial process, or it could happen because the registration request expired. This event
will not fire if a registration record is converted to a full user account record. Instead, a `user.account.create`
event will fire. This is a storage-level event, not a UI event. It should not be used for UI-level actions such as redirects.

 * The subject of the event is set to the registration record begin deleted.

#### `module.users.ui.form_edit.mail_users_search`

A hook-like UI event triggered when the search form is displayed for sending e-mail messages to users. Allows other
modules to intercept and insert their own elements for submission to the search form.

To add elements to the search form, render the output and then add this as an array element to the event's
data array.

This event does not have a subject or arguments.

#### `module.users.ui.form_edit.search`

A hook-like UI event triggered when the users search form is displayed. Allows other
modules to intercept and insert their own elements for submission to the search form.

To add elements to the search form, render the output and then add this as an array element to the event's
data array.

This event does not have a subject or arguments.

USERS MODULE
------------
#### `module.users.config.updated`
Occurs after the Users module configuration has been updated via the administration interface.

GROUPS
------

#### `group.create`
Occurs after a group is created. All handlers are notified. The full group record created is available
as the subject.

#### `group.update`
Occurs after a group is updated. All handlers are notified. The full updated group record is available
as the subject.

#### `group.delete`
Occurs after a group is deleted from the system. All handlers are notified. The full group record
deleted is available as the subject.

#### `group.adduser`
Occurs after a user is added to a group. All handlers are notified. It does not apply to pending
membership requests. The uid and gid are available as the subject.

#### `group.removeuser`
Occurs after a user is removed from a group. All handlers are notified. The uid and gid are
available as the subject.
