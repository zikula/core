Module Specification from Zikula Core 1.3.6
===========================================

.. note::

    **The following document is for guidance only at this
      time and has not been fixed.**


User Upgrade Tasks
------------------

Zikula Core 1.3.6 introduces a lot of forward compatibility for new features
that will come in Zikula 1.4.0.

  - Before uploading the new files please delete the `plugins` and `lib/vendor`
    folders entirely.
  - Upload new files.
  - Make `app/cache` and `app/logs` writable.
  - Run `http://yoursiteurl/upgrade.php`


**The following is for module developers only.**

All of the following changes are optional and forward compatible with
Zikula Core 1.4. Module developers can begin adopting these immediately
without risking any compatibility problems. The reason for these changes
are to allow rapid adoption of various Symfony Components and rapidly
modernize the Core.

There is a refactor tool `zikula-tools` which is referred to in this
document for the purposes of refactoring modules to the new standards
below with very little effort.


Namespaces
----------

Zikula Core 1.3.6 supports PHP namespaces and module should be refactored
for namespace compliance which should MUST be in line with PSR-0 and PSR-1.

In order to be PSR-0 compliant, module the PHP assets in `lib/Modname/*`
need to moved into the module root (see below).

The current specification mandates: (still in dev)
'Foo' is the module name in the examples below and give a few examples of
how module classes should look like:

Controllers:
  - Named like Foo\Controller\UserController
  - Stored in Foo/Controller/UserController.php
  - Example:

        <?php
        namespace FooModule\Controller;

        class UserController extends \Zikula_AbstractController
        {
        }

Apis:
  - Named like Foo\Api\UserApi
  - Stored in Foo/Api/UserApi.php
  - Example:

        <?php
        namespace FooModule\Api;

        class UserApi extends \Zikula_AbstractApi
        {
        }

Entities:
  - Named like Foo\Entity\BarEntity
  - Stored in Foo/Entity/BarEntity.php
  - Example:

        <?php
        namespace FooModule\Entity;

        class BarEntity
        {
        }

Module Structure
----------------

The final structure looks as follows:

    FooModule/
        Api/
            AdminApi.php (was Admin.php)
            UserApi.php (was User.php)
        Controller/
            AdminController.php (was Admin.php)
            UserController.php (was User.php)
        Resources/
            config/
            docs/
            public/
                css/
                images/
                js/
            views/
                plugins/
        FooInstaller.php (was Installer.php)
        FooVersion.php (was Version.php)
        FooModule.php

The last file `FooModule.php` is new and should look like this:

    <?php
    namespace FooModule;

    use Zikula\Bundle\CoreBundle\AbstractModule;

    class FooModule extends AbstractModule
    {
    }

There is a script to restructure the module for you:

    zikula-tools module:restructure --dir=module/MyModule --module=MyModule

You should commit these changes immediately. Your module will continue to work
with the interrim structure created, and you can begin refactoring to namespaces.

.. note::

    It's wise to `git mv` the files to rename the controllers for example before
    making changes to the file contents (should be made in a separate commit).

It is also recommended you place templates in the `Resource/views` folder in a
hierarchy as follows:

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

    zikula-tools module:controller_actions --dir=module/MyModule/Controller

Old method names will continue to work for the time being.


Controller Response
-------------------

Controllers should return a `Symfony\Component\HttpFoundation\Response`.
If you wish to not display the theme, it should emit a
`Zikula\Core\Response\PlainResponse`.

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
  
    - `Zikula\Core\Hook\DisplayHook` (was `Zikula_DisplayHook`).
    - `Zikula\Core\Hook\FilterHook` (was `Zikula_FilterHook`).
    - `Zikula\Core\Hook\ProcessHook` (was `Zikula_ProcessHook`).
    - `Zikula\Core\Hook\ValidationHook` (was `Zikula_ValidationHook`).
  
  - hooks are triggered by `->dispatch($name, $hook)` instead of `->notify($hook)`

Example in Core 1.3.0-1.3.5

    $hook = new Zikula_DisplayHook('hook.name', $id, $url);
    $eventManager->notify($hook);

Example in Core 1.3.6+

    $hook = new \Zikula\Core\Hook\DisplayHook($id, $url);
    $hookDispatcher->dispatch('hook.name', $hook);

New class list:

  - `Zikula\Core\Hook\ValidationProviders` (was `Zikula_Hook_ValidationProviders`).
  - `Zikula\Core\Hook\ValidationResponse` (was `Zikula_Hook_ValidationResponse`).
  - `Zikula\Core\Hook\DisplayResponse` (was `Zikula_Response_DisplayHook`).
  - `Zikula\Core\Hook\AbstractHookListener` (was `Zikula_Hook_AbstractHandler`).


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
