<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Zikula\Bundle\HookBundle\Hook\Hook;

/**
 * Abstract controller for modules.
 *
 * @deprecated
 */
abstract class Zikula_AbstractController extends Zikula_AbstractBase
{
    /**
     * Instance of Zikula_View.
     *
     * @var Zikula_View
     */
    protected $view;

    public function __construct(Zikula_ServiceManager $serviceManager, \Zikula\Core\AbstractModule $bundle = null)
    {
        @trigger_error('Zikula_AbstractController is deprecated, please use AbstractController instead.', E_USER_DEPRECATED);

        parent::__construct($serviceManager, $bundle);

        if ($bundle !== null) {
            // Get bundle from route.
            $module = $bundle->getName();
            // Load module.
            \ModUtil::load($module);

            // Set legacy to true, as the Controller's response will not have the theme around it otherwise.
            // See Zikula\Bundle\CoreBundle\EventListener\ThemeListener::onKernelResponse() - The only place it is used.
            $request = $serviceManager->get("request");
            $request->attributes->set('_legacy', true);
        }
    }

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
     * @param Zikula_View $view Default null means new Render instance for this module name
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
     * @param Hook $hook Hook interface
     *
     * @deprecated since 1.4.0
     * @see self::dispatchHooks()
     *
     * @return Zikula\Bundle\HookBundle\Hook\Hook
     */
    public function notifyHooks(Hook $hook)
    {
        return $this->get('hook_dispatcher')->dispatch($hook->getName(), $hook);
    }

    /**
     * Dispatch hooks.
     *
     * @param Hook $hook Hook interface
     *
     * @return Zikula\Bundle\HookBundle\Hook\Hook
     */
    public function dispatchHooks($name, Hook $hook)
    {
        return $this->get('hook_dispatcher')->dispatch($name, $hook);
    }

    /**
     * Return a response.
     *
     * @param       $text
     * @param null  $code
     * @param array $headers
     *
     * @return Response
     */
    public function response($text, $code = 200, $headers = [])
    {
        return new Response($text, $code, $headers);
    }

    /**
     * Magic method for method_not_found events.
     *
     * @param string $method Method name called
     * @param array  $args   Arguments passed to method call
     *
     * @throws Zikula_Exception_NotFound If method handler cannot be found..
     *
     * @return mixed Data
     */
    public function __call($method, $args)
    {
        $r = $this->getReflection();

        // BC for methods that aren't prefixed with Action
        $method = preg_replace('/(\w+)Action$/', '$1', $method);
        if ($r->hasMethod($method)) {
            return call_user_func_array([$this, $method], $args);
        }

        // BC for default entry point as 'index' if not present try main
        if ($method == 'index' && (false === $r->hasMethod('index') && false === $r->hasMethod('indexAction'))) {
            $method = $r->hasMethod('mainAction') ? 'mainAction' : 'main';

            return call_user_func_array([$this, $method], $args);
        }

        $event = new \Zikula\Core\Event\GenericEvent($this, ['method' => $method, 'args' => $args]);
        $this->eventManager->dispatch('controller.method_not_found', $event);
        if ($event->isPropagationStopped()) {
            return $event->getData();
        }

        throw new Zikula_Exception_NotFound(__f('%1$s::%2$s() does not exist.', [get_class($this), $method]));
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
     * Returns a NotFoundHttpException.
     *
     * This will result in a 404 response code. Usage example:
     *
     *     throw $this->createNotFoundException();
     *
     * @param string     $message  A message
     * @param \Exception $previous The previous exception
     *
     * @return NotFoundHttpException
     */
    public function createNotFoundException($message = null, \Exception $previous = null)
    {
        $message = null === $message ? __('Page not found') : $message;

        return new NotFoundHttpException($message, $previous);
    }
}
