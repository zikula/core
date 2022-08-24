---
currentMenu: permissions
---
# PermissionCheck Annotation

 - Class: `\Zikula\PermissionsBundle\Annotation\PermissionCheck`
 - Also see: `\Zikula\PermissionsBundle\Listener\ControllerPermissionCheckAnnotationReaderListener`

This annotation is used in a Controller Action Method OR Controller Class in one of two ways.

1. Like so: `@PermissionCheck("admin")`
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
    
    Also allowed: `@PermissionCheck("ACCESS_ADMIN")`

2. You can also pass any valid permission schema (e.g. `@PermissionCheck({"ZikulaCategoriesBundle::category", "ID::5", "ACCESS_EDIT"})`.

    - Note the use of curly brackets `{}` within for this type of value.

    The listener will attempt to replace any variable with a route attribute value. For example if the annotation is `@PermissionCheck({"ZikulaGroupsBundle::", "$gid::", "ACCESS_EDIT"})` then the listener will look for an 'gid' attribute in the `Request` object and replace the variable name with its value when testing for permissions.  
    You can also use `$_zkBundle` as the extension name if preferred, e.g. `@PermissionCheck({"$_zkModule::", "$gid::", "ACCESS_EDIT"})`.  
    You can also use the access alias if preferred, e.g. `@PermissionCheck({"$_zkModule::", "$gid::", "edit"})`.

### Please note: You cannot use @PermissionCheck() in *both* the class and the method. This will produce an `AnnotationException`.

---

## Examples:

### Method-level

```php
use Zikula\PermissionsBundle\Annotation\PermissionCheck;
// ...

/**
 * @PermissionCheck({"ZikulaGroupsBundle", "$gid::", "edit"})
 * @Theme("admin")
 * @Template("@ZikulaGroups/Group/edit.html.twig")
 *
 * Modify a group.
 */
#[Route('/admin/edit/{gid}', name: 'zikulagroupsbundle_group_edit', requirements: ['gid' => "^[1-9]\d*$"])]
public function edit(
    Request $request,
    GroupEntity $groupEntity,
    EventDispatcherInterface $eventDispatcher
) { ... }
```

### Class-level

```php
use Zikula\PermissionsBundle\Annotation\PermissionCheck;
// ...

/**
 * @PermissionCheck("admin")
 */
#[Route('/theme')]
class ConfigController extends AbstractController
{
    /**
     * @Theme("admin")
     * @Template("@ZikulaTheme/Config/config.html.twig")
     */
    #[Route('/config', name: 'zikulathemebundle_config_config')]
    public function config()
    {
      // ...
    }
```
