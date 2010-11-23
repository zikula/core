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
 * AbstractBase class for module abstract controllers and apis.
 */
abstract class Zikula_Base implements Zikula_Translatable
{
    /**
     * Name.
     *
     * @var string
     */
    protected $name;

    /**
     * Options (universal constructor).
     *
     * @var array
     */
    protected $options;

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
     * EventManager.
     *
     * @var Zikula_EventManager
     */
    protected $eventManager;

    /**
     * This object's reflection.
     *
     * @var ReflectionObject
     */
    protected $reflection;

    /**
     * Constructor.
     *
     * @param Zikula_ServiceManager $serviceManager ServiceManager instance.
     * @param array                 $options        Options (universal constructor).
     */
    public function __construct(Zikula_ServiceManager $serviceManager, array $options = array())
    {
        $this->serviceManager = $serviceManager;
        $this->eventManager = $this->serviceManager->getService('zikula.eventmanager');
        $this->options = $options;
        Zikula_ClassProperties::load($this, $options);
        $this->_setup();
        $this->_postSetup();

        $this->_setupLanguageDomain();

        $this->postInitialize();
    }

    /**
     * Calculate the translation domain.
     *
     * @return void
     */
    protected function _setupLanguageDomain()
    {
        if ($this->modinfo['type'] == ModUtil::TYPE_MODULE) {
            $this->domain = ZLanguage::getModuleDomain($this->name);
        }
    }

    /**
     * Setup base properties.
     *
     * @return void
     */
    protected function _setup()
    {
        $this->reflection = new ReflectionObject($this);
        $parts = explode('_', $this->reflection->getName());
        $this->name = $parts[0];
        $this->modinfo = ModUtil::getInfoFromName($this->name);
        $modbase = ($this->modinfo['type'] == ModUtil::TYPE_MODULE) ? 'modules' : 'system';
        $this->systemBaseDir = realpath("$modbase/..");
        $this->baseDir = realpath("{$this->systemBaseDir}/$modbase/" . $this->modinfo['directory']);
        $this->libBaseDir = realpath("{$this->baseDir}/lib/" . $this->modinfo['directory']);
    }

    /**
     * Post _setup() in constructor.
     *
     * Intended for base classes.
     *
     * @return void
     */
    protected function _postSetup()
    {

    }

    /**
     * Initialize.
     *
     * Post constructor hook.
     *
     * Intended for base classes.
     *
     * @return void
     */
    protected function initialize()
    {

    }

    /**
     * Get reflection of this object.
     *
     * @return object Reflection.
     */
    public function getReflection()
    {
        return $this->reflection;
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
     * Get options.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Get module info.
     *
     * @return array
     */
    public function getModInfo()
    {
        return $this->modinfo;
    }

    /**
     * Get base directory of this component.
     *
     * @return string
     */
    public function getBaseDir()
    {
        return $this->baseDir();
    }

    /**
     * Get lib/ location for this component.
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
     * Post initialise.
     *
     * Run after construction.  Intended as a post construct hook for child classes.
     *
     * @return void
     */
    protected function postInitialize()
    {

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
     * @throws Zikula_Exception_NotFound Exception.
     *
     * @return void
     */
    protected function throwNotFound($message='', $code=0, $debug=null)
    {
        throw new Zikula_Exception_NotFound($message, $code, $debug);
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
     * @throws Zikula_Exception_NotFound Exception.
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
     * @throws Zikula_Exception_NotFound Exception.
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
     * @throws Zikula_Exception_Forbidden Exception.
     *
     * @return void
     */
    protected function throwForbidden($message='', $code=0, $debug=null)
    {
        throw new Zikula_Exception_Forbidden($message, $code, $debug);
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
     * @throws Zikula_Exception_Forbidden Exception.
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
     * @throws Zikula_Exception_Forbidden Exception.
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
     * @throws Zikula_Exception_Redirect Causing redirect.
     *
     * @return void
     */
    protected function redirect($url, $type = 302)
    {
        throw new Zikula_Exception_Redirect($url, $type);
    }

    /**
     * Cause redirect if $condition by throwing exception which passes to front controller.
     *
     * @param boolean $condition Condition.
     * @param string  $url       Url to redirect to.
     * @param integer $type      Redirect code, 302 default.
     *
     * @throws Zikula_Exception_Redirect Causing redirect.
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
     * @throws Zikula_Exception_Redirect Causing redirect.
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
     * @throws Zikula_Exception If no message is set.
     *
     * @return object This object.
     */
    protected function registerError($message, $type=null, $debug=null)
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
     * @throws Zikula_Exception If no message is set.
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
     * Trigger hookable event.
     * 
     * @param string $name    The event name for the hookable event.
     * @param mixed  $subject The subject of the event.
     * @param array  $args    Extra meta data.
     * @param mixes  $data    Any data to filter.
     * 
     * @return Zikula_Event 
     */
    public function notifyHooks($name, $subject=null, $args=array(), $data=null)
    {
        // set caller's name
        if (!array_key_exists($args['module'])) {
            $args['module'] = $this->name;
        }

        if (!array_key_exists($args['id'])) {
            $args['id'] = 'id';
        }

        $event = new Zikula_Event($name, $subject, $args, $data);
        return $this->eventManager->notify($event);
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
    public function getVar($key, $default=false)
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
     * Return the requested key from input in a safe way.
     *
     * This function is safe to use for recursive arrays and either
     * returns a non-empty string or the (optional) default.
     *
     * This method is based on FormUtil::getPassedValue but array-safe.
     *
     * @param string $key        The field to return.
     * @param mixed  $default    The value to return if the requested field is not found (optional) (default=false).
     * @param string $source     The source field to get a parameter from.
     * @param string $filter     The filter directive to apply, default FILTER_SANITIZE_STRING.
     * @param array  $args       The filter processing args to apply.
     * @param string $objectType The object access path we're getting; used to assign validation errors .
     *
     * @return mixed The requested input key or the specified default.
     */
    public function getInput($key, $default = null, $source = null, $filter = FILTER_SANITIZE_STRING, array $args = array(), $objectType=null)
    {
        return FormUtil::getPassedValue($key, $args, $source, $filter, $args, $objectType);
    }


}