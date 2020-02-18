---
currentMenu: users
---
# Profile interface

Modules that want the Core to identify the module as Profile-capable must provide a class which implements
`\Zikula\UsersModule\ProfileModule\ProfileModuleInterface`.

This interface requires:

```php
/**
 * Display a module-defined user display name (e.g. set by the user) or display the uname as defined by the UserModule
 * If uid is undefined, use CurrentUserApi to check loggedIn status and obtain and use the current user's uid
 *
 * @param int|string $userId The user's id or name
 * @throws InvalidArgumentException if provided $userId is not null and invalid
 */
public function getDisplayName($userId = null): string;

/**
 * Get the url to a user's profile.
 * If uid is undefined, use CurrentUserApi to check loggedIn status and obtain and use the current user's uid
 *
 * @param int|string $userId The user's id or name
 * @throws InvalidArgumentException if provided $userId is not null and invalid
 */
public function getProfileUrl($userId = null): string;

/**
 * Get the avatar image for a given user.
 * If uid is undefined, use CurrentUserApi to check loggedIn status and obtain and use the current user's uid
 *
 * @param int|string $userId The user's id or name
 * @throws InvalidArgumentException if provided $userId is not null and invalid
 */
public function getAvatar($userId = null, array $parameters = []): string;

/**
 * Return the name of the providing bundle.
 */
public function getBundleName(): string;
```

These methods are used in the Core's twig filters - `userAvatar`, `profileLinkByUserId` and `profileLinkByUserName`.
