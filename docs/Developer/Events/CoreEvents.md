---
currentMenu: developer-events
---
# Core events

The `\Zikula\Bundle\CoreBundle\CoreEvents` provides these events:

```php
/**
 * Occurs during Core installation before the modules are installed.
 * Stop propagation of the event to cause the core installer to fail.
 */
public const CORE_INSTALL_PRE_MODULE = 'core.install.pre.module';

/**
 * Occurs during Core upgrade before the modules are upgraded.
 * Stop propagation of the event to cause the core upgrader to fail.
 */
public const CORE_UPGRADE_PRE_MODULE = 'core.upgrade.pre.module';
```
