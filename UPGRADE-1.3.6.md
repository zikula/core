Module Specification from Zikula Core 1.3.6
===========================================

.. note::

    **The following document is for guidance only at this
      time and has not been fixed.**


User Upgrade Tasks
------------------

Zikula Core 1.3.6 introduces a lot of forward compatibility for new features
that will come in Zikula 1.4.0.

  - Before uploading the new files please delete the `system/`, `plugins`/ and
    `lib/vendor/` folders entirely.
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
for namespace compliance which should MUST be in line with PSR-0, PSR-1 and
PSR-2.

In order to be PSR-0 compliant, module the PHP assets in `lib/Modname/*`
need to moved into the module root (see below).

The current specification mandates: (still in dev)
Vendor - PSR-0 mandates a class must contain a vendor. The examples will
illustrate this.

`Foo` is the vendor and 'MyModule' is the module name (`Module` suffic required).
Here are a a few examples of how module classes should look like:

Controllers:
  - Named like `Foo\MyModule\Controller\UserController`
  - Stored in `Foo/MyModule/Controller/UserController.php`
  - Example:

        <?php
        namespace Foo\MyModule\Controller;

        class UserController extends \Zikula_AbstractController
        {
        }

Apis:
  - Named like `Foo\MyModule\Api\UserApi`
  - Stored in `Foo/MyModule/Api/UserApi.php`
  - Example:

        <?php
        namespace Foo\MyModule\Api;

        class UserApi extends \Zikula_AbstractApi
        {
        }

Entities:
  - Named like `Foo\MyModule\Entity\BarEntity`
  - Stored in `Foo/MyModule/Entity/BarEntity.php`
  - Example:

        <?php
        namespace Foo\MyModule\Entity;

        class BarEntity
        {
        }

.. note::

  The namespace can be as deep as required, e.g
  `Zikula\Module\AdminModule` so you might have a class like
  called `Zikula\Module\AdminModule\Controller\AdminController`

            <?php
            namespace Zikula\Module\AdminModule\Controller;

            class AdminController
            {
            }

There is a script to do some of the refactoring for you:

    zikula-tools module:ns --dir=module/MyModule --module=MyModule

Module code must be PSR-1 and PSR-2 compliant. You can fix formatting
with PHP-CS-Fixer: https://github.com/fabpot/PHP-CS-Fixer

PSR-1: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
PSR-2: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md


Naming conventions
------------------

Interfaces and traits names should be suffixed with `Interface` or `Trait`.
Abstract classes should be prefixed with `Abstract`
Generally classes should be suffixed with whatever they are and kept in a
folder. So listeners would be stored in `Listener/` and called `FooListener`.
You can see concrete examples in the module structure section next.


Module Structure
----------------

The final structure looks as follows:

    Foo/
        MyModule/
            Api/
                AdminApi.php (was Admin.php)
                UserApi.php (was User.php)
            Controller/
                AdminController.php (was Admin.php)
                UserController.php (was User.php)
            Entity/
                FooEntity.php
            Listener/
                FooListener.php
            Hook/
                FooHook.php
            Resources/
                config/
                docs/
                locale/
                    foo_module.pot
                public/
                    css/
                    images/
                    js/
                views/
                    Admin/
                        view.tpl
                    User/
                        list.tpl
                        view.tpl
                    plugins/
            Tests/
                AdminControllerTest.php
            vendor/
            MyModuleInstaller.php (was Installer.php)
            MyModuleVersion.php (was Version.php) (todo - this file will go away)
            FooMyModule.php
            CHANGELOG.md
            LICENSE
            README.md
            composer.json       (this file is required, see example)
            phpunit.xml.dist

The last file `FooMyModule.php` is new and should look like this
combining the vendor name (`Foo` with the class name).

    <?php
    namespace Foo\MyModule;

    use Zikula\Core\AbstractModule;

    class FooMyModule extends AbstractModule
    {
    }

.. note::

  The namespace can be as deep as required, e.g
  `Zikula\Module\AdminModule` would result in a class
  called `Zikula\Module\AdminModule\ZikulaAdminModule`

There is a script to restructure the module for you:

    zikula-tools module:restructure --dir=module/MyModule --module=MyModule

You should commit these changes immediately. Your module will continue to work
with the interrim structure created, and you can begin refactoring to namespaces.

.. note::

  It's wise to `git mv` the files to rename/move file before making changes
  to the file contents (which should be made in a separate commit).

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


Composer
--------

Modules and themes must have a `composer.json` manifest which looks like the following:

    {
        "name": "zikula/mailer-module",
        "description": "Mailer Module",
        "type": "zikula-module",
        "license": "LGPL-3.0+",
        "authors": [
            {
                "name": "Zikula",
                "homepage": "http://zikula.org/"
            }
        ],
        "autoload": {
            "psr-0": { "Zikula\\Module\\MailerModule\\": "" }
        },
        "require": {
            "php": ">5.3.3"
        },
        "extra": {
            "zikula": {
                "class": "Zikula\\Module\\MailerModule\\ZikulaMailerModule"
            }
        }
    }

PhpStorm 6 and MOST 0.6.1 have create tools for this.


Controller Methods
------------------

All public controller methods meant to be accessible from the browser request should now be
suffixed with `Action`, so `public function view()` should now read `public function viewAction()`

There is a script to automate this change:

    zikula-tools module:controller_actions --dir=module/MyModule/Controller

Old method names will continue to work for the time being.

The default action should be named `indexAction()` however please not that all routes
must be explicitly referenced so there is in fact no default route any more for a module.


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

    $hook = new Zikula\Core\Hook\DisplayHook($id, $url);
    $hookDispatcher->dispatch('hook.name', $hook);

New class list:

  - `Zikula\Core\Hook\ValidationProviders` (was `Zikula_Hook_ValidationProviders`).
  - `Zikula\Core\Hook\ValidationResponse` (was `Zikula_Hook_ValidationResponse`).
  - `Zikula\Core\Hook\DisplayResponse` (was `Zikula_Response_DisplayHook`).
  - `Zikula\Core\Hook\AbstractHookListener` (was `Zikula_Hook_AbstractHandler`).
  - `Zikula\Component\HookDispatcher\SubscriberBundle` (was `Zikula_HookManager_SubscriberBundle`).
  - `Zikula\Component\HookDispatcher\ProviderBundle` (was `Zikula_HookManager_ProviderBundle`).


Request
-------

The `Request` object is now switched to `Symfony\Component\HttpFoundation\Request`
Please refactor the following calls:

    $request->getGet()->*() becomes $request->query->*()
    $request->getPost()->*() becomes $request->post->*()
    $request->isGet() becomes $request->isMethod('GET')
    $request->isPost() becomes $request->isMethod('POST')

There is a legacy layer in place so the old methods continue to work.

Documentation: http://symfony.com/doc/master/components/http_foundation/introduction.html#request


Gedmo (Doctrine Extensions)
---------------------------
If you use `Sluggable`, you must change the annotation in your Doctrine entities from:

from:

    /**
     * @ORM\Column(name="tag", type="string", length=36)
     * @Gedmo\Sluggable(slugField="slug")
     */
    private $tag;

    /**
     * @ORM\Column(name="slug", type="string", length=128)
     * @Gedmo\Slug
     */
    private $slug;

to:

    /**
     * @ORM\Column(name="tag", type="string", length=36)
     */
    private $tag;

    /**
     * @ORM\Column(name="slug", type="string", length=128)
     * @Gedmo\Slug(fields={"tag"})
     */
    private $slug;


Version.php
-----------

Modules should have `core_min = 1.3.6`.
