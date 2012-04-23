<?php
/**
 * Copyright 2010 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage \Zikula\Core\Core
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Framework\Controller;
use Zikula\Framework\AbstractBase;
use Zikula\Framework\Exception\NotFoundException;
use Zikula\Component\HookDispatcher\Hook;
use Zikula\Core\Event\GenericEvent;
use Zikula_View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Abstract controller for modules.
 */
abstract class AbstractController extends AbstractBase
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
     * @return AbstractController
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
     * @param string $name Name of the hook event.
     * @param Hook   $hook Hook.
     *
     * @return Hook
     */
    public function dispatchHooks($name, Hook $hook)
    {
        return $this->get('hook_dispatcher')->dispatch($name, $hook);
    }

    /**
     * Magic method for method_not_found events.
     *
     * @param string $method Method name called.
     * @param array  $args   Arguments passed to method call.
     *
     * @throws \Zikula_Exception_NotFound If method handler cannot be found..
     *
     * @return mixed Data.
     */
    public function __call($method, $args)
    {
        $event = new GenericEvent($this, array('method' => $method, 'args' => $args));
        $this->dispatcher->dispatch('controller.method_not_found', $event);
        if ($event->isPropagationStopped()) {
            return $event->getData();
        }

        throw new NotFoundException(__f('%1$s::%2$s() does not exist.', array(get_class($this), $method)));
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

    /**
     * Return a response.
     *
     * @param string  $content
     * @param integer $status
     * @param array   $headers
     *
     * @return Response
     */
    public function response($content = '', $status = 200, $headers = array())
    {
        return new Response($content, $status, $headers);
    }

    /**
     * Cause redirect by throwing exception which passes to front controller.
     *
     * @param string  $url  Url to redirect to.
     * @param integer $type Redirect code, 302 default.
     *
     * @return RedirectResponse
     */
    protected function redirect($url, $type = 302)
    {
        return new RedirectResponse($url, $type);
    }
}