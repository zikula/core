---
currentMenu: developer-api
---
# PermissionApi

classname: `\Zikula\PermissionsModule\Api\PermissionApi`.

This class is used to determine whether a user has rights (or permissions) to a given component. Rights are granted
or denied from the Permissions module User Interface. Components/Extensions must declare their Permission structure in
their `composer.json` file.

The class makes the following methods available:

```php
/**
 * Check permissions
 * @api Core-2.0
 */
public function hasPermission(string $component = null, string $instance = null, int $level = ACCESS_NONE, int $user = null): bool;

/**
 * Translation functions
 * Translate level -> name
 * @api Core-2.0
 * @return string|array
 */
public function accessLevelNames(int $level = null);

/**
 * Set permissions for user to false, forcing a reload if called upon again.
 * @api Core-2.0
 */
public function resetPermissionsForUser(int $userId): void;
```

The class is fully tested.

In classes extending `\Zikula\Bundle\CoreBundle\Controller\AbstractController` the following convenience method is available:

```php
/**
 * Convenience shortcut to check if user has requested permissions.
 */
protected function hasPermission(string $component = null, string $instance = null, int $level = null, int $user = null): bool;
```
