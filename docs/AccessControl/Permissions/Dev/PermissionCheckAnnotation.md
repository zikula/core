---
currentMenu: permissions
---
# PermissionCheck Attribute

 - Class: `\Zikula\PermissionsBundle\Annotation\PermissionCheck`
 - Also see: `\Zikula\PermissionsBundle\Listener\ControllerPermissionCheckAnnotationReaderListener`

This annotation is used in a controller method OR class in one of two ways.

1. Like so: `#[PermissionCheck('admin')]`
    Possible alias values are:
      - 'admin'
      - 'delete'
      - 'add'
      - 'edit'
      - 'moderate'
      - 'comment'
      - 'read'
      - 'overview'
    
    In the above cases,
      - the component will be like `AcmeFooBundle::`
      - the instance will be `::`
      - the level will be the corresponding `ACCESS_*` constant (e.g. `ACCESS_ADMIN`)
    
    Also allowed: `#[PermissionCheck('ACCESS_ADMIN')]`

2. You can also pass any valid permission schema (e.g. `#[PermissionCheck(['ZikulaCategoriesBundle::category', 'ID::5', 'ACCESS_EDIT'])]`.

    The listener will attempt to replace any variable with a route attribute value. For example if the annotation is `#[PermissionCheck(['ZikulaGroupsBundle::', '$gid::', 'ACCESS_EDIT'])]` then the listener will look for an 'gid' attribute in the `Request` object and replace the variable name with its value when testing for permissions.  
    You can also use `$_zkBundle` as the extension name if preferred, e.g. `#[PermissionCheck(['$_zkModule::', '$gid::', 'ACCESS_EDIT'])]`.  
    You can also use the access alias if preferred, e.g. `#[PermissionCheck(['$_zkModule::', '$gid::', 'edit'])]`.

### Please note: You cannot use #[PermissionCheck()] in *both* the class and the method. This will produce an `Exception`.

---

## Examples:

### Method-level

```php
use Zikula\PermissionsBundle\Annotation\PermissionCheck;
// ...

/**
 * Modify a group.
 */
#[Route('/admin/edit/{gid}', name: 'zikulagroupsbundle_group_edit', requirements: ['gid' => "^[1-9]\d*$"])]
#[PermissionCheck(['ZikulaGroupsBundle::', '$gid::', 'edit'])]
#[Theme('admin')]
public function edit(
    Request $request,
    GroupEntity $groupEntity,
    EventDispatcherInterface $eventDispatcher
): Response { ... }
```

### Class-level

```php
use Zikula\PermissionsBundle\Annotation\PermissionCheck;
// ...

#[Route('/theme')]
#[PermissionCheck('admin')]
class ConfigController extends AbstractController
{
    #[Route('/config', name: 'zikulathemebundle_config_config')]
    #[Theme('admin')]
    public function config()
    {
      // ...
    }
```
