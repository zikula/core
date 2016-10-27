LocaleApi
=========

classname: \Zikula\SettingsModule\Api\LocaleApi

service id="zikula_settings_module.locale_api"

This class loads the definitions associated with a locale and allows the developer to fetch the properties of that
locale with e.g. `$localeApi->language_direction` or `$localeApi->currency_symbol`. The data is read from the locale's
`.ini` file which is located in `/app/Resources/locale/<locale>/locale.ini`.

The class is available as a Twig global variable as `localeApi` e.g. `{{ localeApi.language_direction }}`.

The class is loaded via an onRequest listener: `\Zikula\SettingsModule\Listener\LocaleListener`

The class makes the following methods available:

    - load($locale, $rootDir)
    - __call($key, $args)
