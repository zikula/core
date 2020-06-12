---
currentMenu: extension-management
---
# Extension events

The `\Zikula\ExtensionsModule\Event` namespace provides several events:

```php
/**
 * Occurs when extension list is viewed and can veto the re-syncing of the extension list.
 * Stop propagation of the event to prevent re-sync.
 */
class ExtensionListPreReSyncEvent

/**
 * Occurs when syncing filesystem to database and new extensions are found and attempted to be inserted.
 * Stop propagation of the event to prevent extension insertion.
 */
class ExtensionEntityPreInsertEvent

/**
 * Occurs before an extension is removed.
 * Stop propagation of the event to prevent extension removal.
 */
class ExtensionEntityPreRemoveEvent

/**
 * Occurs before updating the state of an extension. The event itself cannot affect the workflow unless
 * an exception is thrown to completely halt. For example, performing a permissions check.
 */
class ExtensionPreStateChangeEvent

/**
 * Occurs when a module has been successfully installed but before the Cache has been reloaded.
 */
class ExtensionPostInstallEvent

/**
 * Occurs when a module has been successfully installed
 * and then the Cache has been reloaded after a second Request.
 */
class ExtensionPostCacheRebuildEvent

/**
 * Occurs when a module has been upgraded to a newer version.
 */
class ExtensionPostUpgradeEvent

/**
 * Occurs when a module has been enabled after it was previously disabled.
 */
class ExtensionPostEnabledEvent

/**
 * Occurs when a module has been disabled.
 */
class ExtensionPostDisabledEvent

/**
 * Occurs when a module has been removed entirely.
 */
class ExtensionPostRemoveEvent

/**
 * Occurs when the connections menu for an extension is built.
 */
class ConnectionsMenuEvent
```
