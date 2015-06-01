<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 * 
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_Translate
 *             Please see the NOTICE file distributed with this source code for further
 *             information regarding copyright and licensing.
 */
namespace Zikula\Core\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\AbstractBundle;

abstract class AbstractController extends Controller
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var \Zikula\Common\Translator\Translator
     */
    protected $translator;
    
    /**
     * Constructor.
     *
     * @param AbstractBundle $bundle
     *            A AbstractBundle instance
     * @throws \InvalidArgumentException
     */
    public function __construct(AbstractBundle $bundle)
    {
        $this->name = $bundle->getName();
        $this->translator = $bundle->getContainer()->get('translator');
        $this->translator->setDomain($bundle->getTranslationDomain());
        $this->boot($bundle);
    }

    /**
     * boot the controller
     * 
     * @param AbstractBundle $bundle            
     */
    public function boot(AbstractBundle $bundle)
    {
        // load optional bootstrap
        $bootstrap = $bundle->getPath() . "/bootstrap.php";
        if (file_exists($bootstrap)) {
            include_once $bootstrap;
        }
        // load any plugins
        // @todo adjust this when Namespaced plugins are implemented
        \PluginUtil::loadPlugins($bundle->getPath() . "/plugins", "ModulePlugin_{$this->name}");
    }

    /**
     * Returns a rendered view.
     * 
     * @param string $view
     *            The view name
     * @param array $parameters
     *            An array of parameters to pass to the view
     * @return string The rendered view
     */
    public function renderView($view, array $parameters = array())
    {
        $parameters = $this->decorateTranslator($parameters);
        
        return parent::renderView($view, $parameters);
    }

    /**
     * Renders a view.
     * 
     * @param string $view
     *            The view name
     * @param array $parameters
     *            An array of parameters to pass to the view
     * @param Response $response
     *            A response instance
     * @return Response A Response instance
     */
    public function render($view, array $parameters = array(), Response $response = null)
    {
        $parameters = $this->decorateTranslator($parameters);
        
        return parent::render($view, $parameters, $response);
    }

    /**
     * Streams a view.
     * 
     * @param string $view
     *            The view name
     * @param array $parameters
     *            An array of parameters to pass to the view
     * @param StreamedResponse $response
     *            A response instance
     * @return StreamedResponse A StreamedResponse instance
     */
    public function stream($view, array $parameters = array(), StreamedResponse $response = null)
    {
        $parameters = $this->decorateTranslator($parameters);
        
        return parent::stream($view, $parameters, $response);
    }
    
    /**
     * Decorate translator.
     *
     * @param array $parameters
     *            An array of parameters to pass to the view
     * @return array An array including translator parameters to pass to the view
     */
    protected function decorateTranslator(array $parameters)
    {
        $parameters['domain'] = $this->translator->getDomain();
        return $parameters;
    }

    /**
     * Returns a NotFoundHttpException.
     * This will result in a 404 response code. Usage example:
     * throw $this->createNotFoundException();
     * 
     * @param string $message
     *            A message.
     * @param \Exception $previous
     *            The previous exception.
     * @return NotFoundHttpException
     */
    public function createNotFoundException($message = null, \Exception $previous = null)
    {
        $message = null === $message ? __('Page not found') : $message;
        
        return new NotFoundHttpException($message, $previous);
    }

    /**
     * Returns a AccessDeniedException.
     * This will result in a 403 response code. Usage example:
     * throw $this->createAccessDeniedException();
     * 
     * @param string $message
     *            A message.
     * @param \Exception $previous
     *            The previous exception.
     * @return AccessDeniedException
     */
    public function createAccessDeniedException($message = null, \Exception $previous = null)
    {
        $message = null === $message ? __('Access denied') : $message;
        
        return new AccessDeniedException($message, $previous);
    }

    /**
     * singular translation for modules.
     *
     * @param string $msg Message.
     * @param null $domain
     * @param null $locale
     * @return string
     */
    public function __($msg, $domain = null, $locale = null)
    {
        return $this->translator->__($msg, $domain, $locale);
    }

    /**
     * Plural translations for modules.
     *
     * @param string $m1 Singular.
     * @param string $m2 Plural.
     * @param integer $n Count.
     * @param null $domain
     * @param null $locale
     * @return string
     */
    public function _n($m1, $m2, $n, $domain = null, $locale = null)
    {
        return $this->translator->_n($m1, $m2, $n, $domain, $locale);
    }

    /**
     * Format translations for modules.
     *
     * @param string $msg Message.
     * @param string|array $param Format parameters.
     * @param null $domain
     * @param null $locale
     * @return string
     */
    public function __f($msg, $param, $domain = null, $locale = null)
    {
        return $this->translator->__f($msg, $param, $domain, $locale);
    }

    /**
     * Format plural translations for modules.
     *
     * @param string $m1 Singular.
     * @param string $m2 Plural.
     * @param integer $n Count.
     * @param string|array $param Format parameters.
     * @param null $domain
     * @param null $locale
     * @return string
     */
    public function _fn($m1, $m2, $n, $param, $domain = null, $locale = null)
    {
        return $this->translator->_fn($m1, $m2, $n, $param, $domain, $locale);
    }
}