Dynamic Configuration Dumper
============================

class: `\Zikula\Bundle\CoreBundle\DynamicConfigDumper`

service name: `zikula.dynamic_config_dumper`

The dynamic configuration dumper is intended to be used for dynamically generating Symfony configuration, which
would typically be placed statically in `app/config/config.yml`. The dumper provides methods for setting, getting and
deleting configuration values and parameters. The ZikulaRoutesModule is using it to generate the multilingual routing
configuration for the [JMSI18nRoutingBundle](http://jmsyst.com/bundles/JMSI18nRoutingBundle/master/configuration).
An example configuration might look like so:

    jms_i18n_routing:
        default_locale: en
        locales: [en, de]
        strategy: prefix

As you see, the available languages need to be inserted dynamically, as the user might install additional languages
after the he installed Zikula. The configuration can be changed using the following PHP call:

    $configDumper = $this->get('zikula.dynamic_config_dumper');
    $configDumper->setConfiguration('jms_i18n_routing',
        [
            'default_locale' => $defaultLocale,
            'locales'        => $installedLanguages,
            'strategy'       => $isRequiredLangParameter ? 'prefix' : 'prefix_except_default'
        ]
    );

This will update the configuration file. Note that the configuration becomes active as of **the next page load**.
Use `setParameter()` to set a parameter.

The configuration is written into the `app/config/dynamic/generated.yml` file. Core bundles might specify a default
configuration in the `app/config/dynamic/default.yml` file, which will be used until there is a generated configuration
available.
