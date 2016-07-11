<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Mapstraction plugin definition.
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
        return [
            'displayname' => $this->__('Mapstraction'),
            'description' => $this->__('Provides Mapstraction mapping API library'),
            'version'     => '2.0.17'
        ];
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
