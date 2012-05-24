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
abstract class Zikula_AbstractController extends Zikula_AbstractBase
{
    /**
     * Instance of Zikula_View.
     *
     * @var Zikula_View
     */
    protected $view;

    /**
     * {@inheritdoc}
     */
    protected function initialize()
    {
        $this->configureView();
    }

    /**
     * Create and configure the view for controllers.
     *
     * @return void
     */
    protected function configureView()
    {
        $this->setView();
        $this->view->setController($this);
        $this->view->assign('controller', $this);
    }

    /**
     * Set view property.
     *
     * @param Zikula_View $view Default null means new Render instance for this module name.
     *
     * @return Zikula_AbstractController
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
     * Notify any hookable events.
     *
     * @param Zikula_HookInterface $hook Hook interface.
     *
     * @return Zikula_HookInterface
     */
    public function notifyHooks(Zikula_HookInterface $hook)
    {
        return $this->getService('zikula.hookmanager')->notify($hook);
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
        $this->eventManager->notify($event);
        if ($event->isStopped()) {
            return $event->getData();
        }

        throw new Zikula_Exception_NotFound(__f('%1$s::%2$s() does not exist.', array(get_class($this), $method)));
    }

    /**
     * Predispatch hook, invoked just before requested controller method is dispatched.
     *
     * @return void
     */
    public function preDispatch()
    {
    }

    /**
     * Postdispatch hook, invoked just after requested controller method dispatch returns.
     *
     * @return void
     */
    public function postDispatch()
    {
    }
}
