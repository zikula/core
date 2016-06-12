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


#### `user.gettheme`
Called during UserUtil::getTheme() and is used to filter the results.  Receives arg['type']
with the type of result to be filtered and the $themeName in the $event->data which can
be modified.  Must $event->stopPropagation() if handler performs filter.

#### `user.account.delete`
Occurs after a user is deleted from the system. All handlers are notified. The full user record
deleted is available as the subject. This is a storage-level event, not a UI event. It should not be
used for UI-level actions such as redirects.

 * The subject of the event is set to the user record that is being deleted.

#### `module.users.ui.form_edit.mail_users_search`

A hook-like UI event triggered when the search form is displayed for sending e-mail messages to users. Allows other
modules to intercept and insert their own elements for submission to the search form.

To add elements to the search form, render the output and then add this as an array element to the event's
data array.

This event does not have a subject or arguments.


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
