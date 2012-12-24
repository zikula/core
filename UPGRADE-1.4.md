Upgrading from Core 1.3 to 1.4
==============================

Legacy Layers
-------------

All legacy APIs from the Zikula Core 1.2 series that were supported in 1.3 have
been completely removed as promised.


Module Structure
----------------

It is now possible to place module's PHP assets directly in the module's
root folder, without having to nest in `$modname/lib/$modname`.
Non-PHP assets should now be located in a `Resources/` folder.

The final structure looks as follows:

    MyModule/
        Api/
            Admin.php
            User.php
        Controller/
            Admin.php
            User.php
        Resource/
            config/
            docs/
            public/
                css/
                images/
                js/
            views/
                plugins/
        Installer.php
        Version.php

There is a script to relocate these for you:

    refactor.php zk:migrate_resource --dir=module/MyModule --module=MyModule

The old locations continue to work.

It is recommended you place templates in the `Resource/views` folder in a hierarchy
as follows:

        Resources/
            views/
                Admin/
                    view.tpl
                    list.tpl
                User/
                    view.tpl

This necessitates a change in template calls such as

    $this->view->fetch('Admin/view.tpl');


Controller Methods
------------------

All public controller methods meant to be accessible from the browser request should now be
suffixed with `Action`, so `public function view()` should now read `public function viewAction()`

There is a script to automate this change:

    refactor.php zk:migrate_resource --dir=module/MyModule/Controller

Old method names will continue to work.


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


Version.php
-----------

Modules should have core_min=1.4.0 and core_max=1.4.99 as they specifically will be incompatible
with Zikula Core 1.3