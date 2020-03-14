---
currentMenu: users
---
# CreateUsersApi

Interface: `\Zikula\ZAuthModule\Api\ApiInterface\CreateUsersApiInterface`.  
Class: `\Zikula\UsersModule\Api\CreateUsersApi`.

The CreateUsersApi can be used to create a ZAuth-method User. This can be useful in development contexts where users
must be created. There are methods for creating one user or creating multiple users from an array. Additionally, you
can also validate the values in either a single user array or an array of users arrays.

The class makes the following methods available:

```php
public function createUser(array $userArray): void;

public function createUsers(array $users): array;

public function isValidUserData(array $user);

public function isValidUserDataArray(array $userArrays);

public function persist(): void;

public function getCreatedUsers(): array;

public function getCreatedMappings(): array;

public function clearCreated(): void;
```

The class is fully tested.

The structure of an user array is mandated:

     required keys:
         uname (string)
         pass (string)
         email (string)
     allowed keys:
         activated (int: 0|1 default: 1)
         sendmail (int: 0|1 default: 1)
         groups (a list of int gid separated by |, defaults to Users group)
             does not fail on non-existent groups

Here is an example of how the class might be used:

```php

$api = $container->get(CreateUsersApi::class); // Do not fetch the service from the container, use Dependency Injection.

$userArrays = [/*...my array of user arrays...*/];

$errors = $api->isValidUserDataArray($userArrays);
if (0 === $errors->count()) {
    $api->createUsers($userArrays);
    $api->persist();
}
```
