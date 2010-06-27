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
 * Abstract controller for modules.
 */
abstract class Zikula_Controller extends Zikula_Base
{
    /**
     * Instance of Renderer.
     *
     * @var Renderer
     */
    protected $renderer;

    /**
     * Post Setup hook.
     *
     * @return void
     */
    protected function _postSetup()
    {
        // Create renderer object
        $this->setRenderer();
        $this->renderer->assign('controller', $this);
    }

    /**
     * Set renderer property.
     *
     * @param Renderer $renderer Default null means new Render instance for this module name.
     *
     * @return Zikula_Controller
     */
    protected function setRenderer(Renderer $renderer = null)
    {
        if (is_null($renderer)) {
            $renderer = Renderer::getInstance($this->getName());
        }

        $this->renderer = $renderer;
        return $this;
    }

    /**
     * Get Renderer object for this controller.
     *
     * @return Renderer
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * Magic method for method_not_found events.
     *
     * @param string $method Method name called.
     * @param array  $args   Arguments passed to method call.
     *
     * @throws Zikula_Exception_NotFound If method handler cannot be found..
     *
     * @return mixed Data.
     */
    public function __call($method, $args)
    {
        $event = new Zikula_Event('controller.method_not_found', $this, array('method' => $method, 'args' => $args));
        EventUtil::notifyUntil($event);
        if ($event->hasNotified()) {
            return $event->getData();
        }

        throw new Zikula_Exception_NotFound(__f('%1$s::%2$s() does not exist.', array(get_class($this), $method)));
    }
}