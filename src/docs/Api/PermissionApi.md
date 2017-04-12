PermissionApi
=============

classname: \Zikula\PermissionsModule\Api\PermissionApi

service id="zikula_permissions_module.api.permission"

This class is used to determine whether a user has rights (or permissions) to a given component. Rights are granted
or denied from the Permissions module User Interface. Components/Extensions must declare their Permission structure in
their `composer.json` file.

The class makes the following methods available:

    /**
     * Check permissions
     * @api Core-2.0
     *
     * @param string $component Component
     * @param string $instance Instance
     * @param integer $level Level
     * @param integer $user User Id
     *
     * @return boolean
     */
    public function hasPermission($component = null, $instance = null, $level = ACCESS_NONE, $user = null);

    /**
     * Translation functions
     * Translate level -> name
     * @api Core-2.0
     *
     * @param integer $level Access level
     *
     * @return string Translated access level name
     */
    public function accessLevelNames($level = null);

    /**
     * Set permissions for user to false, forcing a reload if called upon again.
     * @api Core-2.0
     *
     * @param $uid
     */
    public function resetPermissionsForUser($uid);

The class is fully tested.

From classes extending \Zikula\Core\Controller\AbstractController several convenience methods are available:

    /**
     * Convenience shortcut to check if user has requested permissions.
     * @param null $component
     * @param null $instance
     * @param null $level
     * @param null $user
     * @return bool
     */
    public function hasPermission($component = null, $instance = null, $level = null, $user = null);
