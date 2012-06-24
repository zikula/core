UPGRADING MODULES TO CORE 1.4
=============================

This version of Zikula is about migrating previously core technologies out to
Symfony2 and other 3rd party extensions.

## Vendors

Vendor dependencies are managed by Composer. See http://getcomposer.com/

If you work with the core git repository directly, you must run
`php composer.phar install --dev` to get the vendors, and periodically
`php composer.phar update --dev` as necessary to update vendors.

## Module structure

The module structure has changed and classes should be PSR-0 compliant.

```
ExampleModule/
    Api/
        AdminApi.php
    Block/
        ExampleBlock.php
    Controller/
        UserController.php
        AjaxController.php
        AdminController.php
    DependencyInjection/
    Event/
        FooEvent.php
    HookListener/
        FooHookListener.php
    Listener/
        BarListener.php
    Resources/
        config/
            foo.yml
            service.xml
        locale/
            module_example.pot
        public/
            css/
            images/
                admin.png
            js/
        views/
            User/
                foo.html.twig
                index.html.twig
                report.xml.twig
            plugins/
                function.foo.php
            admin.html.twig
            user.html.twig
    Tests/
        Api/
            AdminApiTest.php
        Event/
            FooEventTest.php
        bootstrap.php
    Installer.php
    ExampleEvents.php
    VendorExampleModule.php
    composer.json
```

The `Resources\public` folder contains all web assets.
`Resources\views` is reserved for templates.

  - It is no longer mandatory to use the AbstractController or AbstractApi base
    classes.
  - Controller, Api and Block class names should be suffixed with `Controller`,
    `Api` and `Block` and the files must be named as such.
  - Additional coding standards dictate that classnames should be suffixed with
    what they are, generally taken from the parent folder, e.g. `Event/`
    `FooEvent`, `GetResponseEvent` and so on as with the Controllers, Api and
    Block classes above.
  - Controller methods that respond to HTTP requests should now be suffixed with
    `Action`, e.g. `public function modifyAction()`, the remaining methods will
    not be accessible from the front controller.
  - All controller responses should return a `Symfony\Component\HttpFoundation\Response` object.
  - Controller responses which want to prevent the theme from being rendered
    should return a `Zikula\Framework\Response\PlainResponse`.
  - Files musy be namespaced accordingly with `FooModule`.

## HttpFoundation

Documentation can be found at:

  - http://symfony.com/doc/master/components/http_foundation/index.html

## Event System

EventManager has been deprecated in favour of the Symfony2 EventDispatcher.

All events should now extend from the `Symfony\Component\EventDispatcher\Event`
object. Use `EventDispatcher->dispatch($eventName, $event);`.

`Zikula\Core\Event\GenericEvent` replicates the previous `Zikula_Event` object
but that the main difference is the event name is now passed when dispatching
the event, rather than by creating the event object.

Documentation for the dispatcher can be found at:

 - http://symfony.com/doc/master/components/event_dispatcher/index.html

### Refactoring event calls

For events that relied on `Zikula_Event`.

Before:

    $event = new Zikula_Event('foo', $subject, $args, $data);
    $eventManager->notify($event);

After:

    use Zikula\Core\Event\GenericEvent;

    $event = new GenericEvent($subject, $args, $data);
    $dispatcher->dispatch('foo', $event);

Please note that both the `dispatch()` and `notify()` methods return the event
object so you can do shortcuts as before.

## Dependency Injection

The ServiceManager has been deprecated in favour of Symfony2 DependencyInjection.

Documentation can be found at:

   - http://symfony.com/doc/master/components/dependency_injection/index.html

## Hooks

The API has been normalised to EventDispatchers, so `->notify($hook)` becomes
`dispatch($eventName, $hook)`. The hook objects no longer have the `$name`
parameter in the constructor.

Hooks have moved to the `Zikula\Core\Hook` namespace.

See refactoring examples below:

before::

    $hook = new Zikula_ValidationHook('users.ui_hooks.user.validate_delete', $validators);
    $this->notifyHooks($hook);

after::

    use Zikula\Core\Hook\ValidationHook;

    $hook = new ValidationHook($validators);
    $this->dispatchHooks('users.ui_hooks.user.validate_delete', $hook);

## Templating

TBD

## Repository Layout

The repository layout has been amended as follows:

```
app/
    cache/
    config/
    logs/
src/
web/
vendor/
    hard/
```

The `app/` folder is where the application level file live.
The `src/` folder is where the Zikula specific libraries live.
The `web/` folder is where web facing files live, eventuallu this will be
the front controller and web assets (images, js and css) only.
The `vendor/` folder is where Composer will checkout vendors however the
`vendor/hard` folder is where dependencies not manageable by Composer are
stored in the repository.

## Theme structure

```
ExampleTheme/
    DependencyInjection/
    Listener/
        BarListener.php
    Resources/
        config/
            home.ini
            master.ini
            overrides.yml
            themepalettes.ini
            themevariables.ini
        locale/
            theme_example.pot
        public/
            css/
                style.css
            images/
                example.png
            javascript/
        views/
            blocks/
                lsblock.html.twig
                rsblock.html.twig
            modules/
                Search/
                    admin.html.twig
            plugins/
                function.foo.php
            admin.html.twig
            user.html.twig
    ExampleTheme.php
    version.php
```

## Misc notes

  - The main controller method is now called `indexAction` (renamed from `main`)
    and the default for the 'func' argument is 'index'.
  - Removed `System::redirect()`, use `RedirectResponse()` instead.
