---
currentMenu: extension-management
---
# Core installation events

The `\Zikula\Bundle\CoreInstallerBundle\Event` namespace provides these event classes:

```php
/**
 * Occurs during core installation before the modules are installed.
 * Stop propagation of the event to cause the core installer to fail.
 */
class CoreInstallationPreExtensionInstallation

/**
 * Occurs during core upgrade before the modules are upgraded.
 * Stop propagation of the event to cause the core upgrader to fail.
 */
class CoreUpgradePreExtensionUpgrade
```
