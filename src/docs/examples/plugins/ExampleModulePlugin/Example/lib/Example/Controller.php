<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license MIT
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Controller class.
 */
class ModulePlugin_Users_Example_Controller extends Zikula_Controller_AbstractPlugin
{
    /**
     * Configuration screen.
     *
     * @return string Plugin configuration output.
     */
    public function configure()
    {
        return $this->view->fetch('configure.tpl');
    }
}
