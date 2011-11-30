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

use Zikula\Framework\Plugin\AlwaysOnInterface;
use Zikula\Framework\AbstractPlugin;

/**
 * Doctrine plugin definition.
 */
class SystemPlugin_Imagine_Plugin extends AbstractPlugin implements AlwaysOnInterface
{
    /**
     * Get plugin meta data.
     *
     * @return array Meta data.
     */
    protected function getMeta()
    {
        return array('displayname' => $this->__('Imagine'),
                     'description' => $this->__('Provides Imagine image manipulation library'),
                     'version'     => '1.0.0'
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
        $autoloader = new Zikula_KernelClassLoader();
        $autoloader->spl_autoload_register();
        $autoloader->register('Imagine', dirname(__FILE__) . '/lib/vendor', '\\');
    }
}
