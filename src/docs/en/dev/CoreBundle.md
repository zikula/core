Documentation of the CoreBundle
===============================
  1. [Introduction](#introduction)
  2. [Dynamic configuration dumper](#dynamicconfigdumper)

<a name="introduction" />
Introduction
------------
The CoreBundle is located in lib/Zikula/Bundle/CoreBundle. It provides some useful tools for the new
Symfony integration.

<a name="dynamicconfigdumper" />
The dynamic configuration dumper
--------------------------------
The dynamic configuration dumper is intended to be used for dynamically generating Symfony configuration, which
would be placed statically in `app/config/config.yml` otherwise. The dumper is registered as
`zikula.dynamic_config_dumper` service. It provides methods for setting, getting and deleting configuration values
and paramters. The ZikulaRoutesModule is using it to generate the multilingual routing configuration for the
[JMSI18nRoutingBundle](http://jmsyst.com/bundles/JMSI18nRoutingBundle/master/configuration).
An example configuration might look like so:
```yaml
jms_i18n_routing:
    default_locale: en
    locales: [en, de]
    strategy: prefix
```
As you see, the available languages need to be inserted dynamically, as the user might install additional languages
after the he installed Zikula. The configuration can be changed using the following PHP call:
```php
$configDumper = $this->get('zikula.dynamic_config_dumper');
$configDumper->setConfiguration('jms_i18n_routing',
    array(
        'default_locale' => $defaultLocale,
        'locales'        => $installedLanguages,
        'strategy'       => $isRequiredLangParameter ? 'prefix' : 'prefix_except_default'
    )
);
```
This will update the configuration file. Note that the configuration becomes active as of **the next page load**.
Use `setParameter()` to set a parameter.

The configuration is written into the `app/config/dynamic/generated.yml` file. Core bundles might specify a default
configuration in the `app/config/dynamic/default.yml` file, which will be used until there is a generated configuration
available.
