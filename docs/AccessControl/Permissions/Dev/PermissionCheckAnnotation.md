---
currentMenu: permissions
---
# PermissionCheck Annotation

 - Class: `\Zikula\PermissionsModule\Annotation\PermissionCheck`
 - Also see: `\Zikula\PermissionsModule\Listener\ControllerPermissionCheckAnnotationReaderListener`

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
      - the component will be like `AcmeFooModule::`
      - the instance will be `::`
      - the level will be the corresponding `ACCESS_*` constant (e.g. `ACCESS_ADMIN`)
    
    Also allowed: `@PermissionCheck("ACCESS_ADMIN")`

2. You can also pass any valid permission schema (e.g. `@PermissionCheck({"ZikulaCategoriesModule::category", "ID::5", "ACCESS_EDIT"})`.

    - Note the use of curly brackets `{}` within for this type of value.

    The listener will attempt to replace any variable with a route attribute value. For example if the annotation is `@PermissionCheck({"ZikulaGroupsModule::", "$gid::", "ACCESS_EDIT"})` then the listener will look for an 'gid' attribute in the `Request` object and replace the variable name with its value when testing for permissions.  
    You can also use `$_zkModule` as the extension name if preferred, e.g. `@PermissionCheck({"$_zkModule::", "$gid::", "ACCESS_EDIT"})`.  
    You can also use the access alias if preferred, e.g. `@PermissionCheck({"$_zkModule::", "$gid::", "edit"})`.

### Please note: You cannot use @PermissionCheck() in *both* the Class and the Method. This will produce an AnnotationException.

---

## Examples:

### Method-level

```php
use Zikula\PermissionsModule\Annotation\PermissionCheck;
// ...

/**
 * @Route("/admin/edit/{gid}", requirements={"gid" = "^[1-9]\d*$"})
 * @PermissionCheck({"$_zkModule::", "$gid::", "edit"})
 * @Theme("admin")
 * @Template("@ZikulaGroupsModule/Group/edit.html.twig")
 *
 * Modify a group.
 */
public function editAction(
    Request $request,
    GroupEntity $groupEntity,
    EventDispatcherInterface $eventDispatcher
) { ... }
```

### Class-level

```php
use Zikula\PermissionsModule\Annotation\PermissionCheck;
// ...

/**
 * Class ThemeController
 *
 * @Route("/config")
 * @PermissionCheck("admin")
 */
class ConfigController extends AbstractController
{
    /**
     * @Route("/config")
     * @Theme("admin")
     * @Template("@ZikulaThemeModule/Config/config.html.twig")
     */
    public function configAction() { ... }
```
