Module Specification from Zikula Core 1.4.0
===========================================

  1. [Introduction](#introduction)
  2. [Bootstrap and jQuery](#bootstrapjquery)
  3. [Namespaces](#namespaces)
  4. [Naming conventions](#namingconventions)
  5. [Module Structure](#modulestructure)
  6. [Module composer.json](#modulecomposer)
  7. [Controller Methods](#controllermethods)
  8. [Controller Method Parameters](#controllermethodparameters)
  9. [Controller Response](#controllerresponse)
  10. [Routing](#routing)
  11. [Service Manager](#servicemanager)
  12. [Events](#events)
  13. [Event Names](#eventnames)
  14. [Hooks](#hooks)
  15. [Request](#request)
  16. [Search](#search)
  17. [Gedmo (Doctrine Extensions)](#gedmo)
  18. [Paginate (Doctrine Extensions)](#paginate)
  19. [Version File](#versionfile)
  20. [Persistent Event Listeners](#eventlisteners)
  21. [Theme Standard](#themes)
  22. [Theme composer.json](#themecomposer)
  
<a name="installing" />
Introduction
------------
All of the following changes are optional and forward compatible with
Zikula Core 1.4. Module developers can begin adopting these immediately
without risking any compatibility problems. The reason for these changes
are to allow rapid adoption of various Symfony Components and rapidly
modernize the Core.

There is a refactor tool `zikula-tools` which is referred to in this
document for the purposes of refactoring modules to the new standards
below with very little effort.

<a name="bootstrapjquery" />
Bootstrap and jQuery
--------------------

Zikula now uses Bootstrap 3 with FontAwesome 4 and jQuery.

There are a few small oddities to maintain compatibility with Prototype
but in general it's pretty straightforward. Core modules have been
refactored so there are also working examples in the code.

Documentation: http://zikula.github.io/bootstrap-docs/


<a name="namespaces" />
Namespaces
----------

Zikula Core 1.4.0 supports PHP namespaces and module should be refactored
for namespace compliance which should MUST be in line with PSR-0 or PSR-4; and 
both PSR-1 and PSR-2.

The examples below will use PSR-4.

In order to be PSR-0/4 compliant, module the PHP assets in `lib/Modname/*`
need to moved into the module root (see below).

`Foo` is the vendor and 'MyModule' is the module name (`Module` suffic required).
Here are a a few examples of how module classes should look like:

Controllers:
  - Named like `Foo\MyModule\Controller\UserController`
  - Stored in `foo-my/Controller/UserController.php`
  - Example:

    ```php
    <?php
    namespace Foo\MyModule\Controller;

    class UserController extends \Zikula_AbstractController
    {
    }
    ```

Apis:
  - Named like `Foo\MyModule\Api\UserApi`
  - Stored in `foo-my/Api/UserApi.php`
  - Example:

    ```php
    <?php
    namespace Foo\MyModule\Api;

    class UserApi extends \Zikula_AbstractApi
    {
    }
    ```

Entities:
  - Named like `Foo\MyModule\Entity\BarEntity`
  - Stored in `foo-my/Entity/BarEntity.php`
  - Example:

    ```php
    <?php
    namespace Foo\MyModule\Entity;

    class BarEntity
    {
    }
    ```

.. note::

The namespace can be as deep as required, e.g
`Zikula\Module\AdminModule` so you might have a class like
called `Zikula\Module\AdminModule\Controller\AdminController`

```php
<?php
namespace Zikula\Module\AdminModule\Controller;

class AdminController
{
}
```

There is a script to do some of the refactoring for you:

    zikula-tools module:ns --dir=module/MyModule --vendor=Foo --module=MyModule

Module code must be PSR-1 and PSR-2 compliant. You can fix formatting
with PHP-CS-Fixer: https://github.com/fabpot/PHP-CS-Fixer

PSR-1: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
PSR-2: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md


<a name="namingconventions" />
Naming conventions
------------------

Interfaces and traits names should be suffixed with `Interface` or `Trait`.
Abstract classes should be prefixed with `Abstract`
Generally classes should be suffixed with whatever they are and kept in a
folder. So listeners would be stored in `Listener/` and called `FooListener`.
You can see concrete examples in the module structure section next.


<a name="modulestructure" />
Module Structure
----------------

The final structure looks as follows:

    foo-my/
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
                routing.yml
            docs/
            locale/
                foomymodule.pot
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
        MyModuleVersion.php (was Version.php) (todo - this file may go away)
        FooMyModule.php
        CHANGELOG.md
        LICENSE
        README.md
        composer.json       (this file is required, see example)
        phpunit.xml.dist

The last file `FooMyModule.php` is new and should look like this
combining the vendor name (`Foo` with the class name).

```php
<?php
namespace Foo\MyModule;

use Zikula\Core\AbstractModule;

class FooMyModule extends AbstractModule
{
}
```
.. note::

  The namespace can be as deep as required, e.g
  `Zikula\Module\AdminModule` would result in a class
  called `Zikula\Module\AdminModule\ZikulaAdminModule`

There is a script to restructure the module for you:

    zikula-tools module:restructure --dir=module/MyModule --vendor=Foo --module=MyModule

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

```php
    $this->view->fetch('Admin/view.tpl');
```

<a name="modulecomposer" />
Module composer.json
--------------------

Modules must have a `composer.json` manifest which looks like the following:

```json
{
    "name": "foo/my-module",
    "description": "My Module",
    "type": "zikula-module",
    "license": "LGPL-3.0+",
    "authors": [
        {
            "name": "Example",
            "homepage": "http://example.com/"
        }
    ],
    "autoload": {
        "psr-4": { "Foo\\MyModule\\": "" }
    },
    "require": {
        "php": ">5.3.3"
    },
    "extra": {
        "zikula": {
            "class": "Foo\\MyModule\\FooMyModule"
        }
    }
}
```
PhpStorm 7 and MOST 0.6.1 have create tools for this.


<a name="conrollermethods" />
Controller Methods
------------------

All public controller methods meant to be accessible from the browser request should now be
suffixed with `Action`, so `public function view()` should now read `public function viewAction()`

There is a script to automate this change:

    zikula-tools module:controller_actions --dir=module/MyModule/Controller

Old method names will continue to work for the time being.

The default action should be named `indexAction()` however please not that all routes
must be explicitly referenced so there is in fact no default route any more for a module.


<a name="controllermethodparameters" />
Controller Method Parameters
----------------------------

Arguments of controller methods now automatically receive the request.
For example if ?name=Fred&age=21 then

```php
public function fooAction($name, $age)
{
    return new Response("Hello $name, you are $age");
}
```

Argument order does not matter.

You can also get the request object using this:

```php
public function fooAction(Request $request)
{
    $name = $request->query->get('name');

    return new Response("Hello $name");
}
```

<a name="controllerresponse" />
Controller Response
-------------------

Controllers should return a `Symfony\Component\HttpFoundation\Response`.
If you wish to not display the theme, it should emit a
`Zikula\Core\Response\PlainResponse`.

Zikula will wrap controller return in an appropriate Response.

Documentation: http://symfony.com/doc/master/components/http_foundation/introduction.html#response


<a name="routing" />
Routing
-------

Routing follows standard Symfony routing specifications:

  - http://symfony.com/doc/current/book/routing.html
  - http://symfony.com/doc/current/cookbook/routing/index.html
  - http://symfony.com/doc/current/components/routing/hostname_pattern.html

By default Zikula will look for routing in the module's `Resources/config/routing.yml` file.

```yml
acmeexamplemodule:
    resource: "@AcmeExampleModule/Controller"
    prefix:   /acmeexample
    type:     annotation
```

The file location can be customized or disabled by overriding `getRoutingConfig()` in
the module bundle class (`AcmeExampleModule`).

```php
public function getRoutingConfig()
{
    return "@AcmeExampleModule/Resources/config/routing.yml";
}
```


<a name="servicemanager" />
Service Manager
---------------

This change is internal so is referenced for completeness only.

The Zikula_ServiceManager has been replaced with the Symfony2 Dependency Injection 2.2 component.
Zikula specifically uses the `ContainerBuilder` without compilation.

Documentation: http://symfony.com/doc/master/components/dependency_injection/index.html


<a name="events" />
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

Example in Core 1.3.0-1.3.x

```php
$event = new Zikula_Event('event.name', $subject, $args, $data);
$eventManager->notify($event);
```

Example in Core 1.4.0+

```php
$event = new Zikula\Core\Event\GenericEvent($subject, $args, $data);
$dispatcher->dispatch('event.name', $event);
```

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


<a name="eventnames" />
Event Names
-----------

There are lots of new events you can see them here:

http://symfony.com/doc/current/book/internals.html#events
http://symfony.com/doc/current/components/http_kernel/introduction.html#component-http-kernel-event-table

The following list of even names have been removed:

  - `boostrap.getconfig` - there is no replacement
  - `bootstrap.custom` - there is no replacement
  - `frontcontroller.predispatch` - there is no replacement
  - `frontcontroller.exception` - Subscribe to Kernel::EXCEPTION instead
  - `setup.errorreporting` - there is no replacement
  - `systemerror` - there is no replacement


<a name="hooks" />
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

Example in Core 1.3.0-1.3.x

```php
$hook = new Zikula_DisplayHook('hook.name', $id, $url);
$eventManager->notify($hook);
```

Example in Core 1.4.0+

    $hook = new Zikula\Core\Hook\DisplayHook($id, $url);
    $hookDispatcher->dispatch('hook.name', $hook);

New class list:

  - `Zikula\Core\Hook\ValidationProviders` (was `Zikula_Hook_ValidationProviders`).
  - `Zikula\Core\Hook\ValidationResponse` (was `Zikula_Hook_ValidationResponse`).
  - `Zikula\Core\Hook\DisplayResponse` (was `Zikula_Response_DisplayHook`).
  - `Zikula\Core\Hook\AbstractHookListener` (was `Zikula_Hook_AbstractHandler`).
  - `Zikula\Component\HookDispatcher\SubscriberBundle` (was `Zikula_HookManager_SubscriberBundle`).
  - `Zikula\Component\HookDispatcher\ProviderBundle` (was `Zikula_HookManager_ProviderBundle`).


<a name="request" />
Request
-------

The `Request` object is now switched to `Symfony\Component\HttpFoundation\Request`
Please refactor the following calls:

```php
$request->getGet()->*() becomes $request->query->*()
$request->getPost()->*() becomes $request->post->*()
$request->isGet() becomes $request->isMethod('GET')
$request->isPost() becomes $request->isMethod('POST')
```

There is a legacy layer in place so the old methods continue to work.

Please note the follow APIs have changed (BC break)

```php
$request->.....->filter() // the argument order has changed
$request->files-> // this API now returns an object, not an array
```

Documentation: http://symfony.com/doc/master/components/http_foundation/introduction.html#request


<a name="search" />
Search
------

The Api/methodology for the search module has changed. The previous method of using an Api call is deprecated (but still
fully functional) in favor of a dedicated class that is identified in the Version file's `capabilities` area, like so:

```php
$meta['capabilities'][AbstractSearchable::SEARCHABLE] = array('class' => 'Zikula\Module\UsersModule\Helper\SearchHelper');
```

The `class` key must point to a helper class that extends `Zikula\Module\SearchModule\AbstractSearchable` and defines
both the `getOptions()` method and the `getResults()` method. These two functions are similar to the old Api methods of
similar names, but now have specific parameters set. Please see `AbstractSearchable` for documentation of the parameters.

The main difference now is that the `getResults()` method **MUST** return an array of arrays containing the module's
result set and the sub-arrays **MUST** have keys matching the field names of the
`Zikula\Module\SearchModule\Entity\SearchResultEntity` for merging and persisting the results (be sure to check `sesid`).
**Modules are no longer responsible for persisting their own search results**.

Additional differences include the addition of a `url` field to the result set (results are no longer post-processed),
various services (entityManager, translation, Zikula_View, etc) automatically available in the helper class, etc.
A helper method, `formatWhere()` is available to construct a proper Expr() (search expression) for easy utilization in
your module's search. Also, a helper method, `addError()` is available to provide feedback to the user if their search
is invalid for your module.

The **UsersModule** has implemented the new Search method and can be used as a reference.


<a name="gedmo" />
Gedmo (Doctrine Extensions)
---------------------------
If you use `Sluggable`, you must change the annotation in your Doctrine entities from:

from:

```php
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
```

to:
```php
    /**
     * @ORM\Column(name="tag", type="string", length=36)
     */
    private $tag;

    /**
     * @ORM\Column(name="slug", type="string", length=128)
     * @Gedmo\Slug(fields={"tag"})
     */
    private $slug;
```

<a name="paginate" />
Paginate (Doctrine Extensions)
-----------------------------
The Doctrine Extension Paginate is deprecated. If you are using it, you should refactor it to `Doctrine\ORM\Tools\Pagination\Paginator`.


<a name="versionfile" />
Version File
------------

Modules should have `core_min = 1.4.0`.

You now can add a reason for each dependency. Add a `reason` key to any dependency array you want. Example:
    
```php
$meta['dependencies'] = array(
        array('modname'    => 'Scribite',
              'minversion' => '5.0.0',
              'maxversion' => '',
              'status'     => ModUtil::DEPENDENCY_RECOMMENDED,
              'reason'     => 'Scribite adds a html editor.'),
);
```

*Note: This only works for modules using the new >= 1.4.0 structure. Modules with the < 1.3.x structure are ignoring this setting.*


<a name="eventlisteners" />
Persistent Event Listeners
--------------------------

Persistent event listeners are no longer stored in the database. They should be loaded by 
the DependecyInjection extension.

```php
<?php

namespace My\FooModule\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;

class MyFooExtension extends Extension
{
    /**
     * Responds to the app.config configuration parameter.
     *
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(realpath(__DIR__.'/../Resources/config')));

        $loader->load('services.xml');

    }
}
```

Simply create a service definition and in `Resources/config/services.xml` (or `.yml`)
and `tag` the service with `kernel.event_subscriber`.

```xml
<parameter key="mymodule.foo_listener.class">Path\To\Your\Listener\ClassListener</parameter>

<service id="mymodule.foo_listener" class="%mymodule.foo_listener.class%">
    <tag name="kernel.event_subscriber" />
</service>
```


<a name="themes" />
Theme Standard
--------------

Theme's look very similar to modules.

    foo-my/
        Resources/
            config/
                admin.ini
                home.ini
                master.ini
                overrides.yml
                pageconfigurations.ini
                themepalettes.ini
                themevariables.ini
            docs/
            locale/
                foomytheme.pot
            public/
                css/
                images/
                js/
            views/
                blocks/
                includes/
                modules/
                    ZikulaSearchModule/
                        Block/
                            search.tpl
                admin.tpl
                home.tpl
                master.tpl
                plugins/
        Tests/
        MyThemeVersion.php (was Version.php) (todo - this file may go away)
        FooMyTheme.php
        CHANGELOG.md
        LICENSE
        README.md
        composer.json       (this file is required, see example)
        phpunit.xml.dist


<a name="themecomposer" />
Theme composer.json
-------------------

Themes must have a `composer.json` manifest which looks like the following:

```json
{
    "name": "foo/my-theme",
    "description": "My Theme",
    "type": "zikula-theme",
    "license": "LGPL-3.0+",
    "authors": [
        {
            "name": "Zikula",
            "homepage": "http://zikula.org/"
        }
    ],
    "autoload": {
        "psr-4": { "Foo\\Theme\\MyTheme\\": "" }
    },
    "require": {
        "php": ">5.3.3"
    },
    "extra": {
        "zikula": {
            "class": "Foo\\Theme\\MyTheme\\FooMyTheme"
        }
    }
}
```

.. note::

The chosen namespace can be simplified to Foo\\MyTheme\\
+