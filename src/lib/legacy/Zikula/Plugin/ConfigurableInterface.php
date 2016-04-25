<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Zikula_Plugin_ConfigurableInterface.
 *
 * @deprecated
 */
interface Zikula_Plugin_ConfigurableInterface
{
    /**
     * Return an instance of the configuration controller.
     *
     * Example:
     * <samp>
     *     return new SystemPlugin_Example_Controller($this->serviceManager, array('plugin' => $this));
     * </samp>
     *
     * @return Zikula_Controller_AbstractPlugin
     */
    public function getConfigurationController();
}
