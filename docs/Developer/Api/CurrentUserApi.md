---
currentMenu: developer-api
---
# CurrentUserApi

classname: `\Zikula\UsersModule\Api\CurrentUserApi`.

The CurrentUserApi can be used to obtain the properties of the user operating at runtime. Any property of the UserEntity
is available. For example, to obtain the User id (`uid`) of the current user:

```php
$this->currentUserApi->get('uid')
```

Or to check if the current user is logged in:

```php
if ($this->currentUserApi->isLoggedIn()) {
    return $this->redirectToRoute('zikulausersmodule_account_menu');
}
```

The class makes the following methods available:

```php
/**
 * Check if current user is logged in.
 */
public function isLoggedIn(): bool;

/**
 * Gets a property for the given key.
 *
 * @return mixed
 */
public function get(string $key);
```

The `get` method can be used to acquire any property of the UserEntity.

The class is fully tested.
