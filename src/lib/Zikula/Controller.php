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
     * Instance of Zikula_View.
     *
     * @var Zikula_View
     */
    protected $view;

    /**
     * Post Setup hook.
     *
     * If you want to add to this configuration generally use the postInitialize() hook instead.
     *
     * @return void
     */
    protected function _postSetup()
    {
        // Create renderer object
        $this->setView();
        $this->view->assign('controller', $this);
    }

    /**
     * Set view property.
     *
     * @param Zikula_View $view Default null means new Render instance for this module name.
     *
     * @return Zikula_Controller
     */
    protected function setView(Zikula_View $view = null)
    {
        if (is_null($view)) {
            $view = Zikula_View::getInstance($this->getName());
        }

        $this->view = $view;
        return $this;
    }

    /**
     * Get Zikula_View object for this controller.
     *
     * @return Zikula_View
     */
    public function getView()
    {
        return $this->view;
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

    /**
     * This is a hook to execute before ModUtil::exec() executes any controller method.
     *
     * @return void
     */
    public function preInvokeMethod()
    {
    }
}