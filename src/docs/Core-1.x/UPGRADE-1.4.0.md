Module Compatibility
====================

Maintaining module and theme backward compatibility (BC) with Core-1.3.x is a very high priority for Core-1.4.0.
A very strong effort has been made to keep to the standards of [Semantic Versioning](http://semver.org) which dictate
that BC must be maintained within each major version (1.0.0 until 2.0.0). Therefore, all Core-1.3.x modules and themes
*should* continue to work as expected in Core-1.4.0.

However, because of a necessary upgrade of the Symfony library and despite the development team's best efforts, a few
BC breaks have still occurred:

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

Additional Breaks
-----------------
- The interactive installer functionality has been removed.
- Renamed the `$registrationInfo` field `nickname` to `uname` to be less OpenID specific and more general.
- If a module uses Smarty plugins of another modules the file must be named `modules/Foo/templates/config/usemodules.txt`. In 1.3.x both `usemodules.` and `usemodules.txt` work whereby `usemodules.` has been deprecated since 1.2 and was now removed in 1.4.0 (see #2304 for the details).

Deprecations
============
Zikula Core 1.4.0 moves a lot of code to Symfony-based classes and methods. In this process, **many** original Zikula
Core 1.3.x classes and methods have been deprecated and will be removed in Core 2.0.0. These classes are mostly marked
as `@deprecated` in the code and PHPDoc headers. In addition, several libraries will be removed in Core 2.0.0 and
therefore, developers should refactor their code for the suitable replacement.

<a name="paginate" />
Paginate (Doctrine Extensions)
-----------------------------
The Doctrine Extension Paginate is deprecated. If you are using it, you should refactor it to `Doctrine\ORM\Tools\Pagination\Paginator`.


Forward Compatibility Layer
===========================
All of the following changes are optional and forward compatible with Zikula Core 1.4.0. Module developers can begin
adopting these immediately without risking any compatibility problems. The reason for these changes are to allow
rapid adoption of various Symfony Components and rapidly modernize the Core.

There is a refactor tool, [`zikula-tools`](https://github.com/zikula/Tools) which is referred to in this
document for the purposes of refactoring modules to the new standards below with very little effort.

Module Specification from Zikula Core 1.4.0
===========================================

  1. [Bootstrap and jQuery](#bootstrapjquery)
  2. [Namespaces](#namespaces)
  3. [Naming conventions](#namingconventions)
  4. [Module Structure](#modulestructure)
  5. [Resource loading](#resourceloading)
  6. [Module composer.json](#modulecomposer)
  7. [Controller Methods](#controllermethods)
  8. [Controller Method Parameters](#controllermethodparameters)
  9. [Controller Response](#controllerresponse)
  10. [Routing](#routing)
  11. [Service Manager](#servicemanager)
  12. [Events](#events)
  13. [Event Names](#eventnames)
  14. [Hooks](#hooks)
  15. [ModUrl Deprecated](#modurl)
  16. [Request](#request)
  17. [Search](#search)
  18. [Version File](#versionfile)
  19. [Persistent Event Listeners](#eventlisteners)
  20. [Theme Standard](#themes)
  21. [Theme composer.json](#themecomposer)
  22. [Translation](#translation)


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

`Foo` is the vendor and 'MyModule' is the module name (`Module` suffix required).
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

There is a script (`zikula-tools module:ns`) to do much of the major refactoring for you.
Please see https://github.com/zikula/Tools for more information.

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

To ensure unique script names, all javascript files should be named in the following manner:

    <vendor>.<module>.<controller>.<method>.js

for example:

    Zikula.Dizkus.Admin.Config.js

 - "Short" names of the vendor, bundle, controller and method should be used.
 - Common js libs can be called something like `Zikula.Dizkus.Tools.js` or `Zikula.Dizkus.Common.js`
 - All js code should be placed in `<BundleRoot>/Resources/public/js`


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

There is a script (`zikula-tools module:restructure`) to do much of the major refactoring for you.
Please see https://github.com/zikula/Tools for more information.

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

<a name="resourceloading" />
Resource loading
----------------

1.4.0-compatible extensions should not use Zikula root-relative paths (i.e. `modules/Foo/...`) with
`PageUtil::addVar()` and `{pageaddvar}`. Instead, use Symfony-style paths starting with `@MyModule/Resources/...`.
```smarty
{* Old *}
{pageaddvar name='javascript' value='modules/MyNewsModule/javascript/script.js'}

{* New *}
{pageaddvar name='javascript' value='@MyNewsModule/Resources/public/js/script.js}
```
```php
// Old
PageUtil::addVar('javascript', 'modules/MyNewsModule/javascript/script.js');

// New
PageUtil::addVar('javascript', '@MyNewsModule/Resources/public/js/script.js');
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
        "php": ">5.4.1"
    },
    "extra": {
        "zikula": {
            "class": "Foo\\MyModule\\FooMyModule"
        }
    }
}
```
PhpStorm 7 and MOST 0.6.1 have create tools for this.


<a name="controllermethods" />
Controller Methods
------------------

All public controller methods meant to be accessible from the browser request should now be
suffixed with `Action`, so `public function view()` should now read `public function viewAction()`

There is a script (`zikula-tools module:controller_actions`) to do much of the major refactoring for you.
Please see https://github.com/zikula/Tools for more information.

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

Additionally, Zikula uses the [JMSI18nRoutingBundle](http://jmsyst.com/bundles/JMSI18nRoutingBundle) to have
multilingual and translated routes. The [JMSTranslationBundle](http://jmsyst.com/bundles/JMSTranslationBundle) is included in the core, too, as it is required by the JMSI18nRoutingBundle.

Zikula also uses the [FOSJsRoutingBundle](https://github.com/FriendsOfSymfony/FOSJsRoutingBundle)
to expose routes in javascript files for ajax requests.

By default Zikula will look for routing in the module's `Resources/config/routing.yml` file.
You can configure the routes as stated in the Symfony docs in YAML, PHP or XML and addtionally, due to our use
of the [*SensioFrameworkExtraBundle*](http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/index.html),
in [annotations](http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/routing.html#frameworkextra-annotations-routing-activation).

The file location of the routing configuration file can be customized or disabled by overriding `getRoutingConfig()` in
the module bundle class (`AcmeExampleModule`).

```php
public function getRoutingConfig()
{
    return "@AcmeExampleModule/Resources/config/routing.yml";
}
```
The simplest configuration looks like so:
```yml
acmeexamplemodule:
    resource: "@AcmeExampleModule/Controller"
    type:     annotation
```

However, there are some points you need to take care of when implementing routing in your module:

1. Every module MUST make sure it isn't overriding routes of other modules. This currently is achieved by prefixing
all routes (see *Special Zikula route options* below on how to disable this behaviour) with the url prefix specified
in XYZVersion.php:
   ```php
   public function getMetaData()
   {
       $meta = [];
       ...
       $meta['url'] = $this->__('acmeexample');
       ...

       return $meta;
   }
   ```
2. `ModUtil::url()` will try to generate a new-styled Symfony url if possible. However, This function is deprecated,
for url generation in PHP and Twig take a look at [the Symfony docs](http://symfony.com/doc/current/book/routing.html#generating-urls).
In Smarty the plugin `{modurl}` is deprecated (but functional) and you can now use the `{route}` plugin instead.

### The url styles
As of Zikula 1.4, there are three kinds of url matching / routing styles in Zikula. First, the old ones:
- `index.php?module=bla&type=taa&func=laa&arg1=foo&arg2&bar`: The oldest and ugliest way of urls.
- The current **short urls** known from ZK 1.3.6 and below.

These url styles will be entirely *replaced* by **Symfony Routes**. However a BC layer will keep them working for now.
If you ask "why?", here are some of the advantages of Symfony routing:
- URLs look beautiful (like *yourdomain.com/login* for Login, etc.)
- The current **short urls** are problematic, as it is not possible to determine if the url really exists and it's
  hard to extract the controller from it, etc.
- It's Symfony. This means less Zikula code, more well maintained code.

### Special Zikula route options
These options can be set for any route, see below for an example.
- `zkDescription`, *optional*, default: `""`: You can add a description to every route, explaining the reason for it.
  This description is only shown in the ZikulaRoutesModule admin interface and has no further impact.
- `zkPosition`, *optional*, default: `null`, can be `"top"`, `"bottom"`, `ǹull`: There are currently three "areas" in
  the routes database: top, middle, bottom. Routes added to the top part are parsed before the ones in the middle part,
  which are parsed before the ones in the bottom part. That way, modules get more control over the *weight* of a route.
  For example, the ZikulaRoutesModule adds a route removing trailing slashes. This route is added to the bottom part,
  because it shall do it's work as the very last, to avoid overriding routes of other modules actually requiring a
  trailing slash.
- `zkNoBundlePrefix`, *optional*, default: `false`: If you set this option to true, the bundle prefix will **not** be
  prepended to the route's path. For example, the login route of the ZikulaUsersModule is using this feature to get a
  route like `example.com/login` instead of `example.com/users/login`. **However this feature should only rarely
  be used, as it might collide with other routes.**
- Also notice the options of the [JMSI18nRoutingBundle](http://jmsyst.com/bundles/JMSI18nRoutingBundle/master/usage#leaving-routes-untranslated)
  the core is using.

Yaml example:
```yaml
zikularoutesmodule_redirectingcontroller_removetrailingslash:
    path: /{url}
    defaults: { _controller: ZikulaRoutesModule:Redirecting:removeTrailingSlash }
    requirements:
        url: .*/$
    methods: [GET]
    options:
        zkDescription: "The goal of this route is to redirect URLs with a trailing slash to the same URL without a trailing slash (for example /en/blog/ to /en/blog)."
        zkNoBundlePrefix: true
        zkPosition: "bottom"
        i18n: false
```
Annotation example:
```php
/*
 * @Route("/test", options = {"zkDescription" = "My description"})
 */
```

### ZikulaRoutesModule
The new ZikulaRoutesModule takes care of loading the routes from all Zikula modules. It is saving all the routes of the
modules in a database table and provides them to Symfony using the `RouteLoader.php` file.
The action mainly happens in the `Routing` folder of the module. You'll find the following files:
`InstallerListener.php`, `RouteFinder.php` and `RouteLoader.php`.

1. `InstallerListener.php`:
The installer listener listens to `CoreEvents::MODULE_POSTINSTALL`, `CoreEvents::MODULE_UPGRADE` and
`CoreEvents::MODULE_REMOVE`. Following installation, it searches for any routes in the newly installed module using the
`RouteFinder`. On upgrade, it first deletes all routes of the upgraded module and then re-reads the routes. That way
updated routes are properly added to the database. On uninstall, all routes of the uninstalled module are removed from
database. **Note:** Routes added using the webinterface aren't touched.
2. `RouteFinder.php`:
This class takes care of finding all the routes specified in a module and returning them as a
[RouteCollection](http://api.symfony.com/2.4/Symfony/Component/Routing/RouteCollection.html). If the file specified
in `getRoutingConfig()` (see above) is not present, an empty collection is returned.
3. `RouteLoader.php`:
This service takes care of actually giving Symfony all the routes saved in the ZikulaRoutesModule database.
It is a so-called [custom route loader](http://symfony.com/doc/current/cookbook/routing/custom_route_loader.html).
It's simply loading all the routes from the database and adds them to a new RouteCollection. Additionally, there is
one important task the RouteLoader takes care of: When reading the routes, it also adds the following default parameters
to the route: **_zkModule**, **_zkType** and **_zkFunc**, which are used in `System::queryStringDecode` (see below)
later on. In development mode, this procedure *might* happen on every page load, but it *won't* in production mode.
The RouteLoader is activated in `app/config/routing.yml`:

```yaml
Routing:
    resource: .
    type: zikularoutesmodule
```
That way you *could* also specify your own custom module to take care of routing.

The module also takes care of configuring the [JMSI18nRoutingBundle](http://jmsyst.com/bundles/JMSI18nRoutingBundle/master/configuration),
depending on the installed languages and language options. It provides an api function for reading the current language
settings:
```php
ModUtil::apiFunc('ZikulaRoutesModule', 'admin', 'reloadMultilingualRoutingSettings');
```


### General notes
- You must not add any `default` to your route starting with `_zk`.
- Other than the specified ones, you must not add any `option` to your route starting with `zk`.
- The route names **SHOULD be in format `modname_controllertype_functionname`**, e.g. `acmeexamplemodule_user_index`.
  If you need multiple routes per action, you *might* add a suffix, e.g. `acmeexamplemodule_user_index_1`,
  `acmeexamplemodule_user_index_2`, etc. **It is also possible to use route names like `acme_example_module_user_index`,
  however all route names MUST end with `controllertype_functionname{_suffix}`. Note:** When you use annotations
  to define your routes, you don't have to specify the route's name, as it is auto-calculated.

### Routes in Javascript
- In your ajax controller, you must set the option `"expose"=true` e.g.
```
     * @Route("/thisIsMyRoute", options={"expose"=true})
```
- In your `jQuery.ajax()` call, set the url parameter like so:
```
   url: Routing.generate('acmemigethmakermodule_ajax_methodname'),
```

### Multilingual routes

For making routes translatable please refer to the [JMSI18nRoutingBundle documentation](http://jmsyst.com/bundles/JMSI18nRoutingBundle/master/usage). There is also a dedicated documentation page showing [how to translate your messages](http://jmsyst.com/bundles/JMSTranslationBundle/master/usage) available.

### Expansion of `System::queryStringDecode`
As you probably know, this function tries to calculate the module, type and func parameters from a given url. On top of it, a new section has been added trying to match the current url with a Symfony route. If it succeeds, the Symfony route will take precedence over the old system.
```php
// Try to match a route first.
/** @var \Symfony\Cmf\Component\Routing\ChainRouter $router */
$router = ServiceUtil::get('router');
try {
    $parameters = $router->matchRequest($request);

    if (!isset($parameters['_zkModule']) || !isset($parameters['_zkType']) || !isset($parameters['_zkFunc'])) {
        // This might be the web profiler or another native bundle.
        return;
    } else {
        $request->attributes->set('_zkModule', strtolower($parameters['_zkModule']));
        $request->attributes->set('_zkType', strtolower($parameters['_zkType']));
        $request->attributes->set('_zkFunc', strtolower($parameters['_zkFunc']));
        $request->query->set('module', strtolower($parameters['_zkModule']));
        $request->query->set('type', strtolower($parameters['_zkType']));
        $request->query->set('func', strtolower($parameters['_zkFunc']));
        $request->overrideGlobals();

        return;
    }

} catch (ResourceNotFoundException $e) {
    // This is an old style url.
} catch (RouteNotFoundException $e) {
    // This is an old style url.
}
```
As you see, it fails silently if no route is found and will calculate the parameters the old way instead.
**Note:** You MUST NOT use  any of the `$request->attributes` set here. They are for core internals only and can
be changed or removed at any time.

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

please note that the following events have been deprecated and are scheduled for removal in 2.0.0
 - `bootstrap.getconfig`
 - `bootstrap.custom`
 - `frontcontroller.predispatch`
 - `frontcontroller.exception`
 - `setup.errorreporting`


<a name="eventnames" />
Event Names
-----------

There are lots of new events you can see them here:

http://symfony.com/doc/current/book/internals.html#events
http://symfony.com/doc/current/components/http_kernel/introduction.html#component-http-kernel-event-table

Inside the Zikula core there has been a new event class introduced (Zikula\Core\CoreEvents) which is used
specifically for events which change the state of a module.

    - `module.install` - occurs when a module has been installed.
    - `module.postinstall` - occurs after a module has been installed (on reload of the extensions view)
    - `module.upgrade` - occurs when a module has been upgraded to a newer version.
    - `module.enable` - occurs when a module has been enabled after it has been disabled before.
    - `module.disable` - occurs when a module has been disabled.
    - `module.remove` - occurs when a module has been removed entirely.


<a name="hooks" />
Hooks
-----

Hooks have been altered to use the Symfony2 Event Dispatcher 2.2 component.

The main changes are:

  - Four new Hook objects with no name arg in the constructor:

    - `Zikula\Bundle\HookBundle\Hook\DisplayHook` (was `Zikula_DisplayHook`).
    - `Zikula\Bundle\HookBundle\Hook\FilterHook` (was `Zikula_FilterHook`).
    - `Zikula\Bundle\HookBundle\Hook\ProcessHook` (was `Zikula_ProcessHook`).
    - `Zikula\Bundle\HookBundle\Hook\ValidationHook` (was `Zikula_ValidationHook`).

  - hooks are triggered by `->dispatch($name, $hook)` instead of `->notify($hook)`

Example in Core 1.3.0-1.3.x

```php
$hook = new Zikula_DisplayHook('hook.name', $id, $url);
$eventManager->notify($hook);
```

Example in Core 1.4.0+

    $hook = new Zikula\Bundle\HookBundle\Hook\DisplayHook($id, $url);
    $hookDispatcher->dispatch('hook.name', $hook);

New class list:

  - `Zikula\Bundle\HookBundle\Hook\ValidationProviders` (was `Zikula_Hook_ValidationProviders`).
  - `Zikula\Bundle\HookBundle\Hook\ValidationResponse` (was `Zikula_Hook_ValidationResponse`).
  - `Zikula\Bundle\HookBundle\Hook\DisplayResponse` (was `Zikula_Response_DisplayHook`).
  - `Zikula\Bundle\HookBundle\Hook\AbstractHookListener` (was `Zikula_Hook_AbstractHandler`).
  - `Zikula\Bundle\HookBundle\Bundle\SubscriberBundle` (was `Zikula_HookManager_SubscriberBundle`).
  - `Zikula\Bundle\HookBundle\Bundle\ProviderBundle` (was `Zikula_HookManager_ProviderBundle`).

Notice: in Core-1.4.2 these classes changed again (class aliases are provided for BC):


<a name="modurl" />
ModUrl Deprecated
-----------------

The `ModUrl` class has been deprecated but remains fully functional. Core typehinting for `ModUrl` has been replaced
with `UrlInterface` and a new class, `RouteUrl` has been added (see `\lib\Zikula\Core\`). RouteUrl currently extends
`ModUrl` in order to maintain backward-compatibility, but `RouteUrl` will change in the future to only implement
`UrlInterface`. All usage of typehints for `ModUrl` should be changed to `UrlInterface`.


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
$meta['capabilities'][AbstractSearchable::SEARCHABLE] = ['class' => 'Zikula\UsersModule\Helper\SearchHelper'];
```

The `class` key must point to a helper class that extends `Zikula\SearchModule\AbstractSearchable` and defines
both the `getOptions()` method and the `getResults()` method. These two functions are similar to the old Api methods of
similar names, but now have specific parameters set. Please see `AbstractSearchable` for documentation of the parameters.

The main difference now is that the `getResults()` method **MUST** return an array of arrays containing the module's
result set and the sub-arrays **MUST** have keys matching the field names of the
`Zikula\SearchModule\Entity\SearchResultEntity` for merging and persisting the results (be sure to check `sesid`).
**Modules are no longer responsible for persisting their own search results**.

Additional differences include the addition of a `url` field to the result set (results are no longer post-processed),
various services (entityManager, translation, Zikula_View, etc) automatically available in the helper class, etc.
A helper method, `formatWhere()` is available to construct a proper Expr() (search expression) for easy utilization in
your module's search. Also, a helper method, `addError()` is available to provide feedback to the user if their search
is invalid for your module.

The **UsersModule** has implemented the new Search method and can be used as a reference.

1.4.1 Note: The class was originally implemented as `Zikula\Module\SearchModule\AbstractSearchable` and has been 
refactored as of Core 1.4.1 as `Zikula\SearchModule\AbstractSearchable`. The old class is still functional but deprecated.


<a name="versionfile" />
Version File
------------

Modules should have `core_min = 1.4.0`.

You now can add a reason for each dependency. Add a `reason` key to any dependency array you want. Example:

```php
$meta['dependencies'] = [
    [
        'modname'    => 'Scribite',
        'minversion' => '5.0.0',
        'maxversion' => '',
        'status'     => ModUtil::DEPENDENCY_RECOMMENDED,
        'reason'     => 'Scribite adds a html editor.'
    ]
];
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
        "php": ">5.4.1"
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


<a name="translation" />
Translation
-----------

For changes in translation area please refer to [`zikula-sf-translator`](https://github.com/zikula/core/tree/1.4/src/docs/en/dev/zikula_sf_translator.md#paths_14)

