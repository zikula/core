Upgrading from Core 1.3 to 1.4
==============================

Module Structure
----------------

It is now possible to optionally place module's PHP assets directly in the module's
root folder, without having to nest in `$modname/lib/$modname`.

Service Manager
---------------

This change is internal so is referenced for completeness only.

The Zikula_ServiceManager has been replaced with the Symfony2 Dependency Injection 2.2 component.
Zikula specifically uses the `ContainerBuilder` without compilation.


Events
------

The event system has been switched out for Symfony2 Event Dispatcher 2.2 component.
Zikula specifically uses the `ContainerAwareEventDispatcher`. Please use the Symfony2 API
only. You should change any typehints from `Zikula_EventManager` to `ContainerAwareEventDispatcher`.

The main changes are:

  - the event name is not defined in the constructor of the `Zikula_Event` object.
  - events are triggered by `->dispatch($name, $event)` instead of `->notify($event)`.

Example in Core 1.3.x

    $event = new Zikula_Event('event.name', $subject, $args, $data);
    $eventManager->notify($event);

Example in Core 1.4+

    $event = new Zikula_Event($subject, $args, $data);
    $dispatcher->dispatch('event.name', $event);

You can also create custom events without using the `Zikula_Event` object directly.

Please note, while they will still work, you should also update event method calls if
you use them:

    $event->stop() -> stopPropagation()
    $event->isStopped() -> isPropagationStopped()
    $event->hasArg() -> hasArgument()
    $event->getArg() -> getArgument()
    $event->getArgs() -> getArguments()
    $event->setArg() -> setArgument()
    $event->setArgs() -> setArguments()

Hooks
-----

Hooks have been altered to use the Symfony2 Event Dispatcher 2.2 component.

The main changes are:

  - the hook name is not defined in the constructor of the `Zikula_DisplayHook`,
    `Zikula_ProcessHook`, `Zikula_FilterHook`, `Zikula_validationHook`, objects.
  - events are triggered by `->dispatch($name, $hook)` instead of `->notify($hook)`.

Example in Core 1.3.x

    $hook = new Zikula_DisplayHook('hook.name', $id, $url);
    $eventManager->notify($hook);

Example in Core 1.4+

    $hook = new Zikula_DisplayHook($id, $url);
    $hookDispatcher->dispatch('hook.name', $hook);

