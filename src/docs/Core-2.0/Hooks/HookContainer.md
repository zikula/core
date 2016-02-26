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

In the extension's `install` method, the extension must obtain an instance of its own hook container and register 
the bundles:

```php
    $hookContainer = $this->hookApi->getHookContainerInstance($this->bundle->getMetaData());
    \HookUtil::registerSubscriberBundles($hookContainer->getHookSubscriberBundles());
```

In the `uninstall` method, the extension must do the same (except use `unregister...`):

```php
    $hookContainer = $this->hookApi->getHookContainerInstance($this->bundle->getMetaData());
    \HookUtil::unregisterSubscriberBundles($hookContainer->getHookSubscriberBundles());
```

All the extensions hooks can be stored in the same container and simply defined as either Provider or Subscriber bundles.
If you wish, you may separate the definition bundles by defining different classes in the composer file. If the
HookContainers are separate for each type, then you must specify the type in the instantiation:

```php
    $subscriberHookContainer = $this->hookApi->getHookContainerInstance($this->bundle->getMetaData(), HookApi::SUBSCRIBER_TYPE);
    \HookUtil::registerSubscriberBundles($subscriberHookContainer->getHookSubscriberBundles());

    $providerHookContainer = $this->hookApi->getHookContainerInstance($this->bundle->getMetaData(), HookApi::PROVIDER_TYPE);
    \HookUtil::registerProviderBundles($providerHookContainer->getHookProviderBundles());
```

Please note that install/remove is not 100% finalized for the Core-2.0.0 spec. It is possible that this may eventually
simply be removed altogether and the core will handle installation and removal of an extension's hooks.