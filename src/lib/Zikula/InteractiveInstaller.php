<?php
/**
 * Copyright 2010 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_Core
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Abstract controller for module installer.
 */
abstract class Zikula_InteractiveInstaller extends Zikula_Controller
{
    public function __call($method, $arguments)
    {
        throw new BadMethodCallException(sprintf('%1$s not found in %2$s', $method, get_class($this)));
    }

    /**
     * Post inialise method hook.
     *
     * Enforces admin level permission requirement.  Throws an exception back to the
     * front controller if not.
     *
     * @return void
     */
    public function postInitialize()
    {
        $this->throwForbiddenUnless(SecurityUtil::checkPermission($this->getName . '::', '::', ACCESS_ADMIN), LogUtil::getErrorMsgPermission());
    }
}