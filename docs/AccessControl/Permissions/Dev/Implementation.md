---
currentMenu: permissions
---
# How to implement permissions

## Basic usage

Typically required permissions are checked for using the [PermissionApi](PermissionApi.md).

### In controllers

The `Zikula\Bundle\CoreBundle\Controller\AbstractController\AbstractController` class provides a shortcut method:

- `hasPermission(string $component = null, string $instance = null, int $level = null, int $user = null): bool`

The following code shows a possible example how to use this in a controller method:

```php
namespace Acme\PersonModule\Controller;

use Acme\PersonModule\Entity\PersonEntity;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;

class PersonController extends AbstractController
{
    /**
     * @Route("/admin/edit/{personid}", requirements={"personid" = "^[1-9]\d*$"})
     * @Theme("admin")
     * @Template("@AcmePersonModule/Person/edit.html.twig")
     *
     * Modify a person.
     *
     * @return array|RedirectResponse
     * @throws AccessDeniedException Thrown if the user hasn't permissions to edit the person
     */
    public function editAction(
        Request $request,
        PersonEntity $person
    ) {
        if (!$this->hasPermission('AcmePersonModule::', $person->getId() . '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        // ...
    }
}
```

### Direct usage

Of course the [PermissionApi](PermissionApi.md) can also be injected as a service into any class if desired.

```php
namespace Acme\PersonModule\Helper;

use Acme\PersonModule\Entity\PersonEntity;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

class MyService
{
    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    public function __construct(PermissionApiInterface $permissionApi)
    {
        $this->permissionApi = $permissionApi;
    }

    public function processPerson(PersonEntity $person)
    {
        if (!$this->permissionApi->hasPermission('AcmePersonModule::', $person->getId() . '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }
    }
}
```

## Using an annotation

Controllers may also use a [PermissionCheck Annotation](PermissionCheckAnnotation.md) to perform permission checks in a declarative way.

The controller example from above would look like this then:

```php
namespace Acme\PersonModule\Controller;

use Acme\PersonModule\Entity\PersonEntity;
use Symfony\Component\HttpFoundation\Request;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\PermissionsModule\Annotation\PermissionCheck;

class PersonController extends AbstractController
{
    /**
     * @Route("/admin/edit/{personid}", requirements={"personid" = "^[1-9]\d*$"})
     * @PermissionCheck({"$_zkModule::", "$personid::", "edit"})
     * @Theme("admin")
     * @Template("@AcmePersonModule/Person/edit.html.twig")
     */
    public function editAction(
        Request $request,
        PersonEntity $person
    ) {
        // ...
    }
}
```

Note this is limited to one permission check for each method. It is not possible to have multiple occurrences in a method's doc block. If more complex evaluations are required, permission API should be used instead (see above).

It is also possible to use the annotation on class-level. But it is not allowed to use it for a class *and* for it's methods concurrently.

Example for a class-level use case:

```php
namespace Acme\PersonModule\Controller;

use Acme\PersonModule\Entity\PersonEntity;
use Symfony\Component\HttpFoundation\Request;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\PermissionsModule\Annotation\PermissionCheck;

/**
 * @PermissionCheck("admin")
 */
class ConfigController extends AbstractController
{
    public function someAction(Request $request)
    {
        // ...
    }

    public function otherAction(Request $request)
    {
        // ...
    }
}
```

For more details see the [PermissionCheck Annotation document](PermissionCheckAnnotation.md).

## Twig templates

You can use `hasPermission` inside templates similarly as in PHP. The only difference is that the permission level constants need to be declared as strings.

Example:

```twig
{% if hasPermission('AcmePersonModule::', person.id ~ '::', 'ACCESS_READ') %}
    <h3>{{ person.name }}</h3>
{% endif %}
```

## Special aspects

### Check permissions for specific users

By default permission checks are always executed for the current user. Internally the [PermissionApi](PermissionApi.md) uses the [CurrentUserApi](../../Users/Dev/CurrentUserApi.md) for that.

In order to explicitly perform a permission check for a specific user, it is possible to assign the corresponding user ID as the fourth parameter for the `hasPermission()` method of both `AbstractController` and `PermissionApi`.

Note the annotation-based checks are always done for the current user.

### Permissions for *own* data

The permissions system does not have a default way to express ownerships. The reason for this is that extensions may implement arbitrary logic and rules for when some data is considered as owned by a user.

Common approaches for handling such requirements are for example:

- Use a separate permission component, like `AcmeRecipesModule:Own:(Ingredients|Recipes)`.
- Do a comparison of the current user's ID with the owner ID.
- Use dedicated configuration options, for example user group selectors, to store a group ID which may do additional things (independent of permissions).
- Combine these steps.

### Category-based permissions

If an extension utilises the side-wide category system. it could become helpful to be able to filter data based on permissions for categories.

For further information about this please refer to the [Categories docs](../Integration/Categories/README.md), particularly [CategoryPermissionApi](../Integration/Categories/Dev/CategoryPermissionApi.md).
