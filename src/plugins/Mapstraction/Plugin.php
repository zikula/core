<?php
/**
 * Copyright Zikula Foundation 2011 - Zikula Application Framework
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
 * Doctrine plugin definition.
 */
class SystemPlugin_Mapstraction_Plugin extends Zikula_AbstractPlugin implements Zikula_Plugin_AlwaysOnInterface
{
    /**
     * Get plugin meta data.
     *
     * @return array Meta data.
     */
    protected function getMeta()
    {
        return array('displayname' => $this->__('Mapstraction'),
                     'description' => $this->__('Provides Mapstraction mapping API library'),
                     'version'     => '2.0.17'
                      );
    }

    /**
     * Initialise.
     *
     * Runs at plugin init time.
     *
     * @return void
     */
    public function initialize()
    {
        // nothing to do for now, people just include the JS files
    }
}
