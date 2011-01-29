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
 * SwiftMailer plugin definition.
 */
class SystemPlugin_SwiftMailer_Plugin extends Zikula_Plugin implements Zikula_Plugin_Configurable
{
    /**
     * Get plugin meta data.
     *
     * @return array Meta data.
     */
    protected function getMeta()
    {
        return array('displayname' => $this->__('SwiftMailer'),
                     'description' => $this->__('Provides SwiftMailer'),
                     'version'     => '4.0.6'
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
        // register namespace
        ZLoader::addAutoloader('Swift', dirname(__FILE__) . '/lib/vendor/SwiftMailer/classes');

        // initialize Swift
        require_once realpath($this->baseDir . '/lib/vendor/SwiftMailer/swift_init.php');
    }

    /**
     * Return controller instance.
     *
     * @return Zikula_Plugin_Controller
     */
    public function getConfigurationController()
    {
        return new SystemPlugin_SwiftMailer_Controller($this->serviceManager, array('plugin' => $this));
    }

}
