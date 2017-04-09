PermissionApi
=============

classname: \Zikula\PermissionsModule\Api\PermissionApi

service id="zikula_permissions_module.api.permission"

This class is used to determine whether a user has rights (or permissions) to a given component. Rights are granted
or denied from the Permissions module User Interface. Components/Extensions must declare their Permission structure in
their `composer.json` file.

The class makes the following methods available:

    - hasPermission($component = null, $instance = null, $level = ACCESS_NONE, $user = null)
    - accessLevelNames($level = null)
    - resetPermissionsForUser($uid)
    - getGroupPerms($user = null)

The class is fully tested.

From classes extending \Zikula\Core\Controller\AbstractController several convenience methods are available:

    - hasPermission($component = null, $instance = null, $level = null, $user = null)
