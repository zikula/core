HookManager Documentation
-------------------------

Hooks are special events that are hooked to specific module/plugins via
the persistence layer.  Unlike regular events their load order can be controlled
because they are dispatched via the Zikula_HookManager. Zikula_HookManager depends on
Zikula_EventManager.

The Zikula_HookManager#notify() is designed to be registered as an Zikula_EventManager
eventhandler.

    $eventManager->attach('callhooks', array($hookManager, notify));

The basic idea is this handler receives the hook event and calculates
the actual hook eventhandlers that should be notified, and in which order.

There are four aspects to be aware of when using Hooks:

1. Registering of hook handlers with Zikula_HookManager.
2. Binding of objects (applications/plugins etc.) to hook handlers.
3. Creating the hookable event.
4. Notifying the hooks.

## Registering the hook's event handler.

Applications/plugins should register their hooks with the persistence layer.

    $hookManager->registerHook($hookName, $serviceName, $handlerClass, $handlerMethod);

Notes:
$hookName: This is an hook handler name.
$serviceName: Name of the service to register the handler class by.
$handlerClass: The class that houses the hook handler.
$handlerMethod: This method is the hook handler.

### Hook handler names

This is the final name of the event handlers that handle the hook, so hook names
should be considered like EventManager handler names.

They contain both the hook's owner information and also the type of the hook,
in two parts:

- 'hook.module.$name'
- 'hook.module.$name.plugin.$pluginName'

The second part is the type of the hook, like 'display.view', 'action.create'.

Example of full form could be:
- 'hook.module.foo.display.view'
- 'hook.module.foo.action.create'

When registering the hook handler you must use the full name like
'hook.application.foo.action.create'.

When invoking the hook you just use the hook type: in the above example that would
be 'action.create'.

## Binding Hook handlers with objects

Once hook handlers are registered and managed by the HookManager, they should be
bound to the objects you wish to hook to.  For example, to hook
'Comments' to 'News' when viewing articles.

    $hookManager->bindHook($hookName, $who);

$hookName is the name of the hook handler that was registered.
$who is the name of the object you wish to hook to.

## Example of calling the registered hooks

Simply create the hookable event, then notify the EventManager.  The event should
be named 'callhooks' or whatever the HookManager#notify() handler was called when
attaching to the EventManager.

The event subject must be an instance of HookSubject which simply adds some metadata
along with the real subject of the event. This is used by HookManager to find
the correct hook handlers to notify.

Example

    new Zikula_HookSubject($hookType, $who, $subject);

Real example of notifying a hookable event.

    $event = new Zikula_Event('callhooks', new Zikula_HookSubject('action.create', 'module.foo', $this), $args, $data);
    $eventManager->notify($event);

## Advanced use

HookManager also has a notifyUntil() method (correlating with the counterparts in EventManager).
Hooks can process just like normal events by modifying the $event->data property.  This will be
returned in the event object and can be retrieved by $event->getData();.

Zikula Hook Event Base-Naming Scheme
------------------------------------
hook.systeminit
hook.systemplugin.$name
hook.module.$name
hook.module.$modname.plugin.$name

These named are then followed by the type of hook as required.
