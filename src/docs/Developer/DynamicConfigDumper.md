# Dynamic Configuration Dumper

The dynamic configuration dumper is implemented by the `\Zikula\Bundle\CoreBundle\DynamicConfigDumper` class
and intended to be used for dynamically generating Symfony configuration, which would typically be placed
statically in `config/config.yaml`. The dumper provides methods for setting, getting and deleting
configuration values and parameters.

The ZikulaRoutesModule is using it to generate the multilingual routing configuration for the [JMSI18nRoutingBundle](https://jmsyst.com/bundles/JMSI18nRoutingBundle/master/configuration). An example configuration might look like so:

```yaml
jms_i18n_routing:
    default_locale: en
    locales: [en, de]
    strategy: prefix
```

As you see, the available languages need to be inserted dynamically, as the user might install additional languages
after he installed Zikula. The configuration can be changed using the following PHP call:

```php
$configDumper->setConfiguration('jms_i18n_routing',
    [
        'default_locale' => $defaultLocale,
        'locales'        => $installedLanguages,
        'strategy'       => $isRequiredLangParameter ? 'prefix' : 'prefix_except_default'
    ]
);
```

This will update the configuration file. Note that the configuration becomes active as of **the next page load**.
Use `setParameter()` to set a parameter.

The configuration is written into the `config/dynamic/generated.yaml` file. Core bundles might specify a default
configuration in the `config/dynamic/default.yaml` file, which will be used until there is a generated configuration
available.
