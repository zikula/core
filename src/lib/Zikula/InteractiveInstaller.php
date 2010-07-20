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
    /**
     * Post Setup hook.
     *
     * @return void
     */
    protected function _postSetup()
    {
        // Create renderer object
        $this->setView();
        $this->view->assign('controller', $this);
        $this->view->setCaching(false);
        $this->view->add_core_data();
        $this->view->clear_compiled();
        $this->view->clear_cache();
    }

    /**
     * Dont allow any overrides for this base class.
     *
     * @throws BadMethodCallException If called.
     *
     * @return void
     */
    public function __call($method, $arguments)
    {
        throw new BadMethodCallException(sprintf('%1$s not found in %2$s', $method, get_class($this)));
    }

    /**
     * Ensure we are in an interactive session.
     *
     * @return void
     */
    public function preInvokeMethod()
    {
        $this->throwForbiddenUnless(SecurityUtil::checkPermission($this->getName() . '::', '::', ACCESS_ADMIN), LogUtil::getErrorMsgPermission());
        $check = (bool)(SessionUtil::getVar('interactive_init') || SessionUtil::getVar('interactive_upgrade') || SessionUtil::getVar('interactive_remove'));
        $this->throwForbiddenUnless($check, $this->__('This doesnt appear to be an interactive session.'));
    }
}