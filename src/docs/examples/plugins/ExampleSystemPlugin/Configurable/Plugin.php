<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version.
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Example configurable plugin definition.
 */
class SystemPlugin_Configurable_Plugin extends Zikula_Plugin implements Zikula_Plugin_Configurable, Zikula_Plugin_AlwaysOn
{
    /**
     * Get plugin meta data.
     *
     * @return array Meta data.
     */
    protected function getMeta()
    {
        return array('displayname' => $this->__('Configurable Example'),
                'description' => $this->__('Exmaple of configrable plugin'),
                'version' => '1.0.0'
        );
    }

    /**
     * Initialise.
     *
     * Runs ar plugin init time.
     *
     * @return void
     */
    public function initialize()
    {
    }

    /**
     * Return controller instance.
     *
     * @return Zikula_Controller_Plugin
     */
    public function getConfigurationController()
    {
        return new SystemPlugin_Configurable_Controller($this->serviceManager, array('plugin' => $this));
    }
}
