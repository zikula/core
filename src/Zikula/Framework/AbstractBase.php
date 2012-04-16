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

namespace Zikula\Framework;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use \Zikula\Common\I18n\TranslatableInterface;

/**
 * AbstractBase class for module abstract controllers and apis.
 */
abstract class AbstractBase implements TranslatableInterface
{
    /**
     * Base path of module.
     *
     * @var string
     */
    static protected $path;

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
     * @var \Zikula\Component\DependencyInjection\ContainerBuilder
     */
    protected $container;

    /**
     * EventManager.
     *
     * @var \Symfony\Component\EventDispatcher\EventDispatcher
     */
    protected $dispatcher;

    /**
     * Doctrine EntityManager.
     *
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * Request.
     *
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * Session.
     *
     * @var \Symfony\Component\HttpFoundation\Session
     */
    protected $session;

    /**
     * This object's reflection.
     *
     * @var \ReflectionObject
     */
    protected $reflection;

    /**
     * Constructor.
     *
     * @param ContainerBuilder $container ContainerBuilder instance.
     */
    public function __construct(ContainerBuilder $container)
    {
        $this->container = $container;
        $this->dispatcher = $this->container->get('zikula.eventmanager');

        $this->request = $this->container->get('request');
        $this->session = $this->request->getSession();
        $this->entityManager = $this->container->get('doctrine')->getEntityManager();

        $this->_configureBase();
        $this->initialize();
        $this->postInitialize();
    }

    /**
     * Configure base properties, invoked from the constructor.
     *
     * @return void
     */
    protected function _configureBase()
    {
        $this->getPath();
        $this->systemBaseDir = realpath('.');
        $class = get_class($this);
        $parts = strpos($class, '_') ? explode('_', $class) : explode('\\', $class);
        $this->name = $parts[0];
        $baseDir = \ModUtil::getModuleBaseDir($this->name);
        $this->baseDir = realpath("{$this->systemBaseDir}/$baseDir/" . $this->name);
        if ($baseDir == 'modules') {
            $this->domain = \ZLanguage::getModuleDomain($this->name);
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
     * Gets the base path of the module.
     *
     * @return string
     */
    static public function getPath()
    {
        if (null !== self::$path) {
            return self::$path;
        }

        $reflection = new \ReflectionClass(get_called_class());
        self::$path = dirname($reflection->getFileName());

        return self::$path;
    }

    /**
     * Get reflection of this object.
     *
     * @return \ReflectionObject
     */
    public function getReflection()
    {
        if (!$this->reflection) {
            $this->reflection = new \ReflectionObject($this);
        }

        return $this->reflection;
    }

    /**
     * Get entitymanager.
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
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
     * @return $string Name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the ServiceManager.
     *
     * @return ServiceManager
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Get the EventManager.
     *
     * @return \Symfony\Component\EventDispatcher\EventDispatcher
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
            $this->modinfo = \ModUtil::getInfoFromName($this->name);
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
     * Get top basedir of the component (modules/ system/ etc)/.
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
     * Register status message.
     *
     * Causes a status message to be stored in the session and displayed next pageload.
     *
     * @param string $message Message.
     *
     * @throws \Zikula\Framework\Exception If no message is set.
     *
     * @return object This object.
     */
    protected function registerStatus($message)
    {
        if (!isset($message) || empty($message)) {
            throw new \Zikula\Framework\ExceptionException($this->__f('Empty [%s] received.', 'message'));
        }

        \LogUtil::addStatusPopup($message);

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
     * @throws \Zikula\Framework\Exception If no message is set.
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
     * @throws \Zikula\Framework\Exception If no message is set.
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
     * @throws \Zikula\Framework\Exception If no message is set.
     *
     * @return object This object.
     */
    protected function registerError($message, $type=null, $debug=null)
    {
        if (!isset($message) || empty($message)) {
            throw new \Zikula\Framework\ExceptionException($this->__f('Empty [%s] received.', 'message'));
        }

        \LogUtil::addErrorPopup($message);

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
     * @throws \Zikula\Framework\Exception If no message is set.
     *
     * @return object This object.
     */
    protected function registerErrorIf($condition, $message, $type=null, $debug=null)
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
     * @throws \Zikula\Framework\Exception If no message is set.
     *
     * @return object This object.
     */
    protected function registerErrorUnless($condition, $message, $type=null, $debug=null)
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
    public function setVar($key, $value='')
    {
        \ModUtil::setVar($this->name, $key, $value);
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
        \ModUtil::setVars($this->name, $vars);
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
    public function getVar($key, $default=false)
    {
        return \ModUtil::getVar($this->name, $key, $default);
    }

    /**
     * Convenience Module GetVars for all keys in this module.
     *
     * @return mixed
     */
    public function getVars()
    {
        return \ModUtil::getVar($this->name);
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
        \ModUtil::delVar($this->name, $key);
        return $this;
    }

    /**
     * Convenience Module DelVar for all keys for this module.
     *
     * @return object This.
     */
    public function delVars()
    {
        \ModUtil::delVar($this->name);
        return $this;
    }

    /**
     * Check Csrf token.
     *
     * @param string $token The token, if not set, will pull from $_POST['csrftoken'].
     *
     * @throws \Zikula\Framework\Exception\ForbiddenException If check fails.
     *
     * @return void
     */
    public function checkCsrfToken($token=null)
    {
        if (is_null($token)) {
            $token = $this->request->request->get('csrftoken', false);
        }

        $tokenValidator = $this->container->get('token.validator');

        if (\System::getVar('sessioncsrftokenonetime') && $tokenValidator->validate($token, false, false)) {
            return;
        }

        if ($tokenValidator->validate($token)) {
            return;
        }

        // Should we expire the session also? drak.
        throw new \Zikula\Framework\Exception\ForbiddenException(__('Security token validation failed'));
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

    /**
     * Throw Zikula_Exception_NotFound exception.
     *
     * Used to immediately halt execution.
     *
     * @param string       $message Default ''.
     * @param string       $code    Default 0.
     * @param string|array $debug   Debug information.
     *
     * @throws \Zikula\Framework\Exception\NotFoundException Exception.
     *
     * @return void
     */
    protected function throwNotFound($message='', $code=0, $debug=null)
    {
        throw new \Zikula\Framework\Exception\NotFoundException($message, $code, $debug);
    }

    /**
     * Throw Zikula_Exception_NotFound exception if $condition.
     *
     * Used to immediately halt execution if $condition.
     *
     * @param bool         $condition Condition.
     * @param string       $message   Default ''.
     * @param string       $code      Default 0.
     * @param string|array $debug     Debug information.
     *
     * @throws \Zikula\Framework\Exception\NotFoundException Exception.
     *
     * @return void
     */
    protected function throwNotFoundIf($condition, $message='', $code=0, $debug=null)
    {
        if ($condition) {
            $this->throwNotFound($message, $code, $debug);
        }
    }

    /**
     * Throw Zikula_Exception_NotFound exception unless $condition.
     *
     * Used to immediately halt execution unless $condition.
     *
     * @param bool         $condition Condition.
     * @param string       $message   Default ''.
     * @param string       $code      Default 0.
     * @param string|array $debug     Debug information.
     *
     * @throws \Zikula\Framework\Exception\NotFoundException Exception.
     *
     * @return void
     */
    protected function throwNotFoundUnless($condition, $message='', $code=0, $debug=null)
    {
        if (!$condition) {
            $this->throwNotFound($message, $code, $debug);
        }
    }

    /**
     * Throw Zikula_Exception_Forbidden exception.
     *
     * Used to immediately halt execution.
     *
     * @param string       $message Default ''.
     * @param string       $code    Default 0.
     * @param string|array $debug   Debug information.
     *
     * @throws \Zikula\Framework\Exception\ForbiddenException Exception.
     *
     * @return void
     */
    protected function throwForbidden($message='', $code=0, $debug=null)
    {
        throw new \Zikula\Framework\Exception\ForbiddenException($message, $code, $debug);
    }

    /**
     * Throw Zikula_Exception_Forbidden exception if $condition.
     *
     * Used to immediately halt execution if condition.
     *
     * @param bool         $condition Condition.
     * @param string       $message   Default ''.
     * @param string       $code      Default 0.
     * @param string|array $debug     Debug information.
     *
     * @throws \Zikula\Framework\Exception\ForbiddenException Exception.
     *
     * @return void
     */
    protected function throwForbiddenIf($condition, $message='', $code=0, $debug=null)
    {
        if ($condition) {
            $this->throwForbidden($message, $code, $debug);
        }
    }

    /**
     * Throw Zikula_Exception_Forbidden exception unless $condition.
     *
     * Used to immediately halt execution unless condition.
     *
     * @param bool         $condition Condition.
     * @param string       $message   Default ''.
     * @param string       $code      Default 0.
     * @param string|array $debug     Debug information.
     *
     * @throws \Zikula\Framework\Exception\ForbiddenException Exception.
     *
     * @return void
     */
    protected function throwForbiddenUnless($condition, $message='', $code=0, $debug=null)
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
     * @throws \Zikula\Framework\Exception\RedirectException Causing redirect.
     *
     * @return void
     */
    protected function redirect($url, $type = 302)
    {
        throw new \Zikula\Framework\Exception\RedirectException($url, $type);
    }

    /**
     * Cause redirect if $condition by throwing exception which passes to front controller.
     *
     * @param boolean $condition Condition.
     * @param string  $url       Url to redirect to.
     * @param integer $type      Redirect code, 302 default.
     *
     * @throws \Zikula\Framework\Exception\RedirectException Causing redirect.
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
     * @throws \Zikula\Framework\Exception\RedirectException Causing redirect.
     *
     * @return void
     */
    protected function redirectUnless($condition, $url, $type = 302)
    {
        if (!$condition) {
            $this->redirect($url, $type);
        }
    }
}
