<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\PermissionsModule\Api\ApiInterface;

interface PermissionApiInterface
{
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
}
