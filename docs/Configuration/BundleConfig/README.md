---
currentMenu: bundle-config
---
# Bundle config

As of Zikula 3.1.0, all bundle config options that were previously stored in `services_custom.yaml` are now stored
in standard Symfony config objects. The largest advantage of this approach is that default values can be easily
set in php and do not need to be stored in yaml files. Therefore only non-default values must be set in the config
objects. As of 3.1.0, the following bundles/extensions have config objects in use:

 - CoreBundle ([default values](Defaults/core.yaml))
 - ZikulaRoutesModule ([default values](Defaults/zikula_routes.yaml))
 - ZikulaSecurityCenterModule ([default values](Defaults/zikula_security_center.yaml))
 - ZikulaSettingsModule ([default values](Defaults/zikula_settings.yaml))
 - ZikulaThemeModule ([default values](Defaults/zikula_theme.yaml))

These files are stored in the `config/packages` directory. If these files do not exist in your installation, do not be
alarmed. The file will only exist if **non-default** values have been set previously. If only default values are in use,
the file is removed because it is unneeded.

In most situations, these files can be safely ignored. The values within will be updated when config changes are made
via the UI within the Zikula admin settings. It is permissible, however, for advanced users to alter the
values of these files manually. When this is done, the cache must be manually cleared for new values to function.
Be warned; manual alteration of *any* config value can have unintended side effects including complete disabling of
your website.
