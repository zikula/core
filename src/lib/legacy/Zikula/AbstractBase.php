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
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\AbstractModule;

/**
 * AbstractBase class for module abstract controllers and apis.
 *
 * @deprecated
 */
abstract class Zikula_AbstractBase implements Zikula_TranslatableInterface, ContainerAwareInterface
{
    /**
     * Name.
     *
     * @var string
     */
    protected $name;

    /**
     * Base dir.
     *
     * @var string
     */
    protected $baseDir;

    /**
     * System basedir.
     *
     * @var string
     */
    protected $systemBaseDir;

    /**
     * Component's lib/ base dir.
     *
     * @var string
     */
    protected $libBaseDir;

    /**
     * Modinfo.
     *
     * @var array
     */
    protected $modinfo;

    /**
     * Translation domain.
     *
     * @var string|null
     */
    protected $domain = null;

    /**
     * ServiceManager.
     *
     * @var Zikula_ServiceManager
     */
    protected $serviceManager;

    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * EventManager.
     *
     * @var Zikula_EventManager
     */
    protected $eventManager;

    /**
     * @var ContainerAwareEventDispatcher
     */
    private $dispatcher;

    /**
     * Doctrine EntityManager.
     *
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * Request.
     *
     * @var Zikula_Request_Http
     */
    protected $request;

    /**
     * This object's reflection.
     *
     * @var ReflectionObject
     */
    protected $reflection;

    /**
     * @var \Zikula\Core\AbstractModule
     */
    protected $bundle;

    /**
     * Constructor.
     *
     * @param Zikula_ServiceManager $serviceManager ServiceManager instance.
     * @param AbstractModule        $bundle
     */
    public function __construct(Zikula_ServiceManager $serviceManager, AbstractModule $bundle = null)
    {
        $this->setContainer($serviceManager);
        $this->dispatcher = $this->getContainer()->get('event_dispatcher');
        $this->eventManager = $this->dispatcher;
        $this->request =  \ServiceUtil::get('request');
        $this->entityManager = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $this->_configureBase($bundle);
        $this->initialize();
        $this->postInitialize();
    }

    /**
     * Configure base properties, invoked from the constructor.
     *
     * @param $bundle
     *
     * @return void
     */
    protected function _configureBase($bundle = null)
    {
        $this->systemBaseDir = realpath('.');

        if (null !== $bundle) {
            $this->name = $bundle->getName();
            $this->domain = ZLanguage::getModuleDomain($this->name);
            $this->baseDir = $bundle->getPath();
        } else {
            $separator = (false === strpos(get_class($this), '_')) ? '\\' : '_';
            $parts = explode($separator, get_class($this));
            $this->name = $parts[0];
            $baseDir = ModUtil::getModuleBaseDir($this->name);
            $this->baseDir = $this->libBaseDir = realpath("{$this->systemBaseDir}/$baseDir/" . $this->name);
            if (realpath("{$this->baseDir}/lib/" . $this->name)) {
                $this->libBaseDir = realpath("{$this->baseDir}/lib/" . $this->name);
            }
            if ($baseDir == 'modules') {
                $this->domain = ZLanguage::getModuleDomain($this->name);
            }
        }
    }

    /**
     * Initialize: called from constructor.
     *
     * Intended for initialising base classes.
     *
     * @return void
     */
    protected function initialize()
    {
    }

    /**
     * Post initialise: called from constructor.
     *
     * Intended for child classes.
     *
     * @return void
     */
    protected function postInitialize()
    {
    }

    /**
     * Get reflection of this object.
     *
     * @return ReflectionObject
     */
    public function getReflection()
    {
        if (!$this->reflection) {
            $this->reflection = new ReflectionObject($this);
        }

        return $this->reflection;
    }

    /**
     * Get entitymanager.
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->getContainer()->get('doctrine.orm.default_entity_manager');
    }

    /**
     * Get translation domain.
     *
     * @return string|null
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Get name.
     *
     * @return string Name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the ServiceManager.
     *
     * @deprecated since 1.4.0
     *
     * @return Zikula_ServiceManager
     */
    public function getServiceManager()
    {
        return $this->container;
    }

    /**
     * Get the ServiceManager.
     *
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Get the EventManager.
     *
     * @deprecated since 1.4.0
     * @see self::getDispatcher()
     *
     * @return ContainerAwareEventDispatcher
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * Get the EventManager.
     *
     * @return ContainerAwareEventDispatcher
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * Get module info.
     *
     * @return array
     */
    public function getModInfo()
    {
        if (!$this->modinfo) {
            $this->modinfo = ModUtil::getInfoFromName($this->name);
        }

        return $this->modinfo;
    }

    /**
     * Get base directory of this component.
     *
     * @return string
     */
    public function getBaseDir()
    {
        return $this->baseDir;
    }

    /**
     * Get lib/ location for this component.
     *
     * @deprecated since 1.4.0
     *
     * @return string
     */
    public function getLibBaseDir()
    {
        return $this->libBaseDir;
    }

    /**
     * Get top basedir of the component (modules/ system/ etc)/.
     *
     * @deprecated since 1.4.0
     *
     * @return string
     */
    public function getSystemBaseDir()
    {
        return $this->systemBaseDir;
    }

    /**
     * Translate.
     *
     * @param string $msgid String to be translated.
     *
     * @return string
     */
    public function __($msgid)
    {
        return __($msgid, $this->domain);
    }

    /**
     * Translate with sprintf().
     *
     * @param string       $msgid  String to be translated.
     * @param string|array $params Args for sprintf().
     *
     * @return string
     */
    public function __f($msgid, $params)
    {
        return __f($msgid, $params, $this->domain);
    }

    /**
     * Translate plural string.
     *
     * @param string $singular Singular instance.
     * @param string $plural   Plural instance.
     * @param string $count    Object count.
     *
     * @return string Translated string.
     */
    public function _n($singular, $plural, $count)
    {
        return _n($singular, $plural, $count, $this->domain);
    }

    /**
     * Translate plural string with sprintf().
     *
     * @param string       $sin    Singular instance.
     * @param string       $plu    Plural instance.
     * @param string       $n      Object count.
     * @param string|array $params Sprintf() arguments.
     *
     * @return string
     */
    public function _fn($sin, $plu, $n, $params)
    {
        return _fn($sin, $plu, $n, $params, $this->domain);
    }

    /**
     * Throw Exception\NotFoundHttpException exception.
     *
     * Used to immediately halt execution.
     *
     * @param string       $message Default ''.
     * @param integer       $code    Default 0.
     * @param string|array $debug   Debug information.
     *
     * @throws Exception\NotFoundHttpException exception.
     * @deprecated since 1.4.0
     *
     * @return void
     */
    protected function throwNotFound($message = '', $code = 0, $debug = null)
    {
        throw new Exception\NotFoundHttpException($message, null, $code);
    }

    /**
     * Throw Exception\NotFoundHttpException if $condition.
     *
     * Used to immediately halt execution if $condition.
     *
     * @param bool         $condition Condition.
     * @param string       $message   Default ''.
     * @param integer       $code      Default 0.
     * @param string|array $debug     Debug information.
     *
     * @throws Exception\NotFoundHttpException Exception.
     * @deprecated since 1.4.0
     * @return void
     */
    protected function throwNotFoundIf($condition, $message = '', $code = 0, $debug = null)
    {
        if ($condition) {
            $this->throwNotFound($message, $code, $debug);
        }
    }

    /**
     * Throw Exception\NotFoundHttpException exception unless $condition.
     *
     * Used to immediately halt execution unless $condition.
     *
     * @param bool         $condition Condition.
     * @param string       $message   Default ''.
     * @param integer       $code      Default 0.
     * @param string|array $debug     Debug information.
     *
     * @throws Exception\NotFoundHttpException Exception.
     * @deprecated since 1.4.0
     *
     * @return void
     */
    protected function throwNotFoundUnless($condition, $message = '', $code = 0, $debug = null)
    {
        if (!$condition) {
            $this->throwNotFound($message, $code, $debug);
        }
    }

    /**
     * Throw AccessDeniedException exception.
     *
     * Used to immediately halt execution.
     *
     * @param string       $message Default ''.
     * @param integer       $code    Default 0.
     * @param string|array $debug   Debug information.
     *
     * @throws AccessDeniedException Exception.
     * @deprecated since 1.4.0
     *
     * @return void
     */
    protected function throwForbidden($message = '', $code = 0, $debug = null)
    {
        throw new AccessDeniedException($message, $debug, $code);
    }

    /**
     * Throw AccessDeniedException exception if $condition.
     *
     * Used to immediately halt execution if condition.
     *
     * @param bool         $condition Condition.
     * @param string       $message   Default ''.
     * @param integer       $code      Default 0.
     * @param string|array $debug     Debug information.
     *
     * @throws AccessDeniedException Exception.
     *
     * @return void
     */
    protected function throwForbiddenIf($condition, $message = '', $code = 0, $debug = null)
    {
        if ($condition) {
            $this->throwForbidden($message, $code, $debug);
        }
    }

    /**
     * Throw AccessDeniedException exception unless $condition.
     *
     * Used to immediately halt execution unless condition.
     *
     * @param bool         $condition Condition.
     * @param string       $message   Default ''.
     * @param integer       $code      Default 0.
     * @param string|array $debug     Debug information.
     *
     * @throws AccessDeniedException Exception.
     * @deprecated since 1.4.0
     *
     * @return void
     */
    protected function throwForbiddenUnless($condition, $message = '', $code = 0, $debug = null)
    {
        if (!$condition) {
            $this->throwForbidden($message, $code, $debug);
        }
    }

    /**
     * Cause redirect by throwing exception which passes to front controller.
     *
     * @param string  $url  Url to redirect to.
     * @param integer $type Redirect code, 302 default.
     *
     * sends RedirectResponse Causing redirect.
     * @deprecated since 1.4.0 return a RedirectResponse instead!
     *
     * @return void
     */
    protected function redirect($url, $type = 302)
    {
        $response = new RedirectResponse(System::normalizeUrl($url), $type);
        $response->send();
        exit;
    }

    /**
     * Cause redirect if $condition by throwing exception which passes to front controller.
     *
     * @param boolean $condition Condition.
     * @param string  $url       Url to redirect to.
     * @param integer $type      Redirect code, 302 default.
     *
     * sends RedirectResponse Causing redirect.
     *
     * @return void
     */
    protected function redirectIf($condition, $url, $type = 302)
    {
        if ($condition) {
            $this->redirect($url, $type);
        }
    }

    /**
     * Cause redirect unless $condition by throwing exception which passes to front controller.
     *
     * @param boolean $condition Condition.
     * @param string  $url       Url to redirect to.
     * @param integer $type      Redirect code, 302 default.
     *
     * sends RedirectResponse Causing redirect.
     *
     * @return void
     */
    protected function redirectUnless($condition, $url, $type = 302)
    {
        if (!$condition) {
            $this->redirect($url, $type);
        }
    }

    /**
     * Register status message.
     *
     * Causes a status message to be stored in the session and displayed next pageload.
     *
     * @param string $message Message.
     *
     * @deprecated since 1.4.0
     *
     * @throws Zikula_Exception If no message is set.
     *
     * @return object This object.
     */
    protected function registerStatus($message)
    {
        if (!isset($message) || empty($message)) {
            throw new Zikula_Exception($this->__f('Empty [%s] received.', 'message'));
        }

        LogUtil::addStatusPopup($message);

        return $this;
    }

    /**
     * Register status message if $condition.
     *
     * Causes a status message to be stored in the session and displayed next pageload.
     *
     * @param boolean $condition Condition.
     * @param string  $message   Message.
     *
     * @throws Zikula_Exception If no message is set.
     *
     * @deprecated since 1.4.0
     *
     * @return object This object.
     */
    protected function registerStatusIf($condition, $message)
    {
        if ($condition) {
            return $this->registerStatus($message);
        }

        return $this;
    }

    /**
     * Register status message if $condition.
     *
     * Causes a status message to be stored in the session and displayed next pageload.
     *
     * @param boolean $condition Condition.
     * @param string  $message   Message.
     *
     * @deprecated since 1.4.0
     *
     * @throws Zikula_Exception If no message is set.
     *
     * @return object This object.
     */
    protected function registerStatusUnless($condition, $message)
    {
        if (!$condition) {
            return $this->registerStatus($message);
        }

        return $this;
    }

    /**
     * Register error message.
     *
     * Causes a error message to be stored in the session and displayed next pageload.
     *
     * @param string  $message Message.
     * @param integer $type    Type.
     * @param mixed   $debug   Debug.
     *
     * @deprecated since 1.4.0
     *
     * @throws Zikula_Exception If no message is set.
     *
     * @return object This object.
     */
    protected function registerError($message, $type = null, $debug = null)
    {
        if (!isset($message) || empty($message)) {
            throw new Zikula_Exception($this->__f('Empty [%s] received.', 'message'));
        }

        LogUtil::addErrorPopup($message);

        return $this;
    }

    /**
     * Register error message if $condition.
     *
     * Causes a error message to be stored in the session and displayed next pageload.
     *
     * @param boolean $condition Condition.
     * @param string  $message   Message.
     * @param integer $type      Type.
     * @param mixed   $debug     Debug.
     *
     * @throws Zikula_Exception If no message is set.
     *
     * @return object This object.
     */
    protected function registerErrorIf($condition, $message, $type = null, $debug = null)
    {
        if ($condition) {
            return $this->registerError($message, $type, $debug);
        }

        return $this;
    }

    /**
     * Register error message if $condition.
     *
     * Causes a error message to be stored in the session and displayed next pageload.
     *
     * @param boolean $condition Condition.
     * @param string  $message   Message.
     * @param integer $type      Type.
     * @param mixed   $debug     Debug.
     *
     * @deprecated since 1.4.0
     *
     * @throws Zikula_Exception If no message is set.
     *
     * @return object This object.
     */
    protected function registerErrorUnless($condition, $message, $type = null, $debug = null)
    {
        if (!$condition) {
            return $this->registerError($message, $type, $debug);
        }

        return $this;
    }

    /**
     * Convenience Module SetVar.
     *
     * @param string $key   Key.
     * @param mixed  $value Value, default empty.
     *
     * @return object This.
     */
    public function setVar($key, $value = '')
    {
        ModUtil::setVar($this->name, $key, $value);

        return $this;
    }

    /**
     * Convenience Module SetVars.
     *
     * @param array $vars Array of key => value.
     *
     * @return object This.
     */
    public function setVars(array $vars)
    {
        ModUtil::setVars($this->name, $vars);

        return $this;
    }

    /**
     * Convenience Module GetVar.
     *
     * @param string  $key     Key.
     * @param boolean $default Default, false if not found.
     *
     * @return mixed
     */
    public function getVar($key, $default = false)
    {
        return ModUtil::getVar($this->name, $key, $default);
    }

    /**
     * Convenience Module GetVars for all keys in this module.
     *
     * @return mixed
     */
    public function getVars()
    {
        return ModUtil::getVar($this->name);
    }

    /**
     * Convenience Module DelVar.
     *
     * @param string $key Key.
     *
     * @return object This.
     */
    public function delVar($key)
    {
        ModUtil::delVar($this->name, $key);

        return $this;
    }

    /**
     * Convenience Module DelVar for all keys for this module.
     *
     * @return object This.
     */
    public function delVars()
    {
        ModUtil::delVar($this->name);

        return $this;
    }

    /**
     * Check Csrf token.
     *
     * @param string $token The token, if not set, will pull from $_POST['csrftoken'].
     *
     * @throws AccessDeniedException If check fails.
     *
     * @return void
     */
    public function checkCsrfToken($token = null)
    {
        $this->get('zikula_core.common.csrf_token_handler')->validate($token);
    }

    /**
     * Returns a AccessDeniedException.
     *
     * This will result in a 403 response code. Usage example:
     *
     *     throw $this->createAccessDeniedException();
     *
     * @param string     $message  A message.
     * @param \Exception $previous The previous exception.
     *
     * @return AccessDeniedException
     */
    public function createAccessDeniedException($message = null, \Exception $previous = null)
    {
        $message = null === $message ? __('Access denied') : $message;

        return new AccessDeniedException($message, $previous);
    }

    /**
     * Convenience to get a service.
     *
     * @param string $id Service Name.
     *
     * @return mixed Service or null.
     */
    protected function get($id)
    {
        return $this->container->get($id);
    }

    /**
     * Convenience to get a service.
     *
     * @param string $id Service Name.
     *
     * @deprecated since 1.4.0
     *
     * @return mixed Service or null.
     */
    protected function getService($id)
    {
        return $this->container->get($id);
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->serviceManager = $container;
        $this->container = $container;
    }

    /**
     * Convenience hasService shortcut.
     *
     * @param string $id Service name.
     *
     * @deprecated since 1.4.0
     *
     * @return boolean
     */
    protected function hasService($id)
    {
        return $this->container->has($id);
    }

    /**
     * Convenience hasService shortcut.
     *
     * @param string $id Service name.
     *
     * @return boolean
     */
    protected function has($id)
    {
        return $this->container->has($id);
    }
}
