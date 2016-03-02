HookContainer
=============

Extensions that wish to utilize Hooks must define a HookContainer class that extends 
`\Zikula\Bundle\HookBundle\AbstractHookContainer`. The class must implement one method `setupHookBundles()` which
instantiates and registers hook bundles.

````php
    protected function setupHookBundles()
    {
        $bundle = new SubscriberBundle('ZikulaSpecModule', 'subscriber.user.ui_hooks.view.content', 'ui_hooks', $this->__('Foo'));
        $bundle->addEvent('foo_view', 'zikula_spec_module.ui_hooks.foo_view');
        $this->registerHookSubscriberBundle($bundle);
    }
```

See `src/docs/en/dev/Hooks` for more information on Hooks.


Installation of Hooks
---------------------

In the extension's `install` method, the extension must utilize the HookApi like so:

```php
    $this->hookApi->installSubscriberHooks($this->bundle->getMetaData());
    // and|or
    $this->hookApi->installProviderHooks($this->bundle->getMetaData());
```

In the `uninstall` method, the extension must implement:

```php
    $this->hookApi->uninstallSubscriberHooks($this->bundle->getMetaData());
    // and|or
    $this->hookApi->uninstallProviderHooks($this->bundle->getMetaData());
```