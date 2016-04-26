Documentation of the CoreBundle
===============================
  1. [Introduction](#introduction)
  2. [Dynamic configuration dumper](#dynamicconfigdumper)
  3. [Cache clearer](#cacheclearer)

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
    [
        'default_locale' => $defaultLocale,
        'locales'        => $installedLanguages,
        'strategy'       => $isRequiredLangParameter ? 'prefix' : 'prefix_except_default'
    ]
);
```
This will update the configuration file. Note that the configuration becomes active as of **the next page load**.
Use `setParameter()` to set a parameter.

The configuration is written into the `app/config/dynamic/generated.yml` file. Core bundles might specify a default
configuration in the `app/config/dynamic/default.yml` file, which will be used until there is a generated configuration
available.

<a name="cacheclearer" />
The cache clearer
--------------------------------
The cache clearer is intended to be used for clearing (parts of) the Symfony cache. The cache clearer is registered as
`zikula.cache_clearer` service. It provides one method: `clear($type)`. `$type` determines what part of the cache
shall be deleted. Currently, three types are supported:

1. `symfony.routing.generator`: Deletes the url generator files.
2. `symfony.routing.matcher`:   Deletes the url matcher files.
3. `symfony.routing.fosjs`:     Deletes the cache files for route generation in javascript (using the FOSJsRoutingBundle)
4. `symfony.config`: Deletes the container configuration cache files.

**Note:** You can also specify `symfony.routing` to delete the url generator AND matcher files.
Usage example:
```php
$cacheClearer = $this->get('zikula.cache_clearer');
$cacheClearer->clear('symfony.config');
```
