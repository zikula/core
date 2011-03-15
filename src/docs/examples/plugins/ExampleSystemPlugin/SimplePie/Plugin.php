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
 * ZFeed plugin definition.
 */
class SystemPlugin_SimplePie_Plugin extends Zikula_AbstractPlugin
{
    /**
     * Get plugin meta data.
     *
     * @return array Meta data.
     */
    protected function getMeta()
    {
        return array('displayname' => $this->__('SimplePie Plugin'),
                     'description' => $this->__('Provides SimplePie.'),
                     'version'     => '1.2.1'
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
        include_once dirname(__FILE__) . '/lib/vendor/SimplePie/SimplePie.compiled.php';
        include_once dirname(__FILE__) . '/lib/SimplePieFeed.php';
    }
}
