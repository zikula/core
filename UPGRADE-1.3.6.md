Module Specification from Zikula Core 1.3.6
===========================================

.. note::

    The following document is for guidance only at this time and has not been fixed.


User Upgrade Tasks
------------------

Please delete the `plugins/Doctrine` and `plugins\DoctrineExtensions` folders entirely and then
run `http://yoursiteurl/upgrade.php`



**The following is for module developers only.**


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

The old locations continue to work for the time being.

It is recommended you place templates in the `Resource/views` folder in a hierarchy
as follows:

        Resources/
            views/
                Admin/
                    view.tpl
                    list.tpl
                User/
                    view.tpl

This necessitates a change in template calls such as:

    $this->view->fetch('Admin/view.tpl');


Controller Methods
------------------

All public controller methods meant to be accessible from the browser request should now be
suffixed with `Action`, so `public function view()` should now read `public function viewAction()`

There is a script to automate this change:

    refactor.php zk:migrate_resource --dir=module/MyModule/Controller

Old method names will continue to work for the time being.


Controller Response
-------------------

Controllers should return a `Symfony\Component\HttpFoundation\Response`.
If you wish to not display the theme, it should emit a
`Zikula\Framework\Response\PlainResponse`.

Zikula will wrap controller return in an appropriate Response.

Documentation: http://symfony.com/doc/master/components/http_foundation/introduction.html#response


Service Manager
---------------

This change is internal so is referenced for completeness only.

The Zikula_ServiceManager has been replaced with the Symfony2 Dependency Injection 2.2 component.
Zikula specifically uses the `ContainerBuilder` without compilation.

Documentation: http://symfony.com/doc/master/components/dependency_injection/index.html


Events
------

The event system has been switched out for Symfony2 Event Dispatcher 2.2 component.
Zikula specifically uses the `ContainerAwareEventDispatcher`. Please use the Symfony2 API
only. You should change any typehints from `Zikula_EventManager` to `ContainerAwareEventDispatcher`.

The main changes are:

  - Listener priorities are reversed. Higher numbers are executed first. When attaching
    listeners using the `Zikula_EventManager::attach()` API is fully BC, and translates
    the priorities to the `EventDispatcher` standard.

  - Introduced a new generic event object called `Zikula\Core\Event\GenericEvent`.
    This is compatible with `Zikula_Event` and you should switch to using it immediately.
    
  - Events are triggered by `->dispatch($name, $event)` instead of `->notify($event)`.

Example in Core 1.3.0-1.3.5

    $event = new Zikula_Event('event.name', $subject, $args, $data);
    $eventManager->notify($event);

Example in Core 1.3.6+

    $event = new Zikula\Core\Event\GenericEvent($subject, $args, $data);
    $dispatcher->dispatch('event.name', $event);

Please note, while they will still work, you should also update event method calls if
you use them:

    $event->stop() ======= $event->stopPropagation()
    $event->isStopped() == $event->isPropagationStopped()
    $event->hasArg() ===== $event->hasArgument()
    $event->getArg() ===== $event->getArgument()
    $event->getArgs() ==== $event->getArguments()
    $event->setArg() ===== $event->setArgument()
    $event->setArgs() ==== $event->setArguments()

Documentation: http://symfony.com/doc/master/components/event_dispatcher/introduction.html


Hooks
-----

Hooks have been altered to use the Symfony2 Event Dispatcher 2.2 component.

The main changes are:

  - Four new Hook objects with no name arg in the constructor:
  
    `Zikula\Core\Hook\DisplayHook`
    `Zikula\Core\Hook\FilterHook`
    `Zikula\Core\Hook\ProcessHook`
    `Zikula\Core\Hook\ValidationHook`
  
  They are backward compatible with the existing `Zikula_DisplayHook`, 
  `Zikula_ProcessHook`, `Zikula_FilterHook`, and `Zikula_validationHook`, hooks
  and you can switch to using them immediately.
    
  - hooks are triggered by `->dispatch($name, $hook)` instead of `->notify($hook)`

Example in Core 1.3.0-1.3.5

    $hook = new Zikula_DisplayHook('hook.name', $id, $url);
    $eventManager->notify($hook);

Example in Core 1.3.6+

    $hook = new \Zikula\Core\Hook\DisplayHook($id, $url);
    $hookDispatcher->dispatch('hook.name', $hook);


Request
-------

The `Request` object is now switched to `Symfony\Component\HttpFoundation\Request`
Please refactor the following calls:

    $request->getGet()-> becomes $request->query->
    $request->getPost()-> becomes $request->post->
    $request->isGet() becomes $request->isMethod('GET')
    $request->isPost() becomes $request->isMethod('POST')

There is a legacy layer in place so the old methods continue to work.

Documentation: http://symfony.com/doc/master/components/http_foundation/introduction.html#request


Version.php
-----------

Modules should have core_min=1.3.6
