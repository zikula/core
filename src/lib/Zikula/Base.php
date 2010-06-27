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
abstract class Zikula_Base
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
     * @var object
     */
    protected $serviceManager;

    /**
     * EventManager.
     *
     * @var object
     */
    protected $eventManager;

    /**
     * This object's reflection.
     *
     * @var object
     */
    protected $reflection;

    /**
     * Constructor.
     *
     * @param Zikula_ServiceManager $serviceManager ServiceManager instance.
     * @param Zikula_EventManager   $eventManager   EventManager instance.
     * @param array                 $options        Options (universal constructor).
     */
    public function __construct(Zikula_ServiceManager $serviceManager, Zikula_EventManager $eventManager, array $options = array())
    {
        $this->serviceManager = $serviceManager;
        $this->eventManager = $eventManager;
        $this->options = $options;
        $this->_setup();
        $this->_postSetup();

        if ($this->modinfo['type'] == ModUtil::TYPE_MODULE) {
            $this->domain = ZLanguage::getModuleDomain($this->name);
        }

        $this->postInitialize();
    }

    /**
     * Setup base properties.
     *
     * @return void
     */
    private function _setup()
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
    protected function __f($msgid, $params)
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
    protected function _n($singular, $plural, $count)
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
    protected function _fn($sin, $plu, $n, $params)
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
     * Post construct setup.
     *
     * Intended for base classes.
     *
     * @return void
     */
    protected function _postSetup()
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

        $msgs = SessionUtil::getVar('_ZStatusMsg', array());

        $msgs[] = DataUtil::formatForDisplayHTML((string)$message);

        SessionUtil::setVar('_ZStatusMsg', $msgs);

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

        $showDetailInfo = (System::isInstalling() || (System::isDevelopmentMode() && SecurityUtil::checkPermission('.*', '.*', ACCESS_ADMIN)));

        if ($showDetailInfo) {
            $bt = debug_backtrace();

            $cf0 = $bt[0];
            $cf1 = isset($bt[1]) ? $bt[1] : array('function' => '', 'args' => '');
            $file = $cf0['file'];
            $line = $cf0['line'];
            $func = !empty($cf1['function']) ? $cf1['function'] : '';
            $class = !empty($cf1['class']) ? $cf1['class'] : '';
            $args = $cf1['args'];
        } else {
            $func = '';
        }

        $message = DataUtil::formatForDisplayHTML((string)$message);
        if (!$showDetailInfo) {
            $msg = $message;
        } else {
            // TODO A [do we need to have HTML sanitization] (drak)
            $func = ((!empty($class)) ? "$class::$func" : $func);
            $msg = __f('%1$s The origin of this message was \'%2$s\' at line %3$s in file \'%4$s\'.', array($message, $func, $line, $file));
            if (System::isDevelopmentMode()) {
                $msg .= '<br />';
                $msg .= _prayer($debug);
                $msg .= '<br />';
                $msg .= _prayer(debug_backtrace());

            }
        }

        $msgs = SessionUtil::getVar('_ZErrorMsg', array());
        $msgs[] = DataUtil::formatForDisplayHTML($message);

        SessionUtil::setVar('_ZErrorMsg', $msgs);

        // check if we've got an error type
        if (isset($type) && is_numeric($type)) {
            SessionUtil::setVar('_ZErrorMsgType', $type);
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
    protected function registerErrorIf($condition, $message, $type=null, $debug=null)
    {
        if ($condition) {
            return $this->registerError($message, $type, $debug);
        }
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
    }

    /**
     * Carry out hook operations for module.
     *
     * @param string  $hookobject The object the hook is called for - one of 'item', 'category' or 'module'.
     * @param string  $hookaction The action the hook is called for - one of 'new', 'create', 'modify', 'update', 'delete', 'transform', 'display', 'modifyconfig', 'updateconfig'.
     * @param integer $hookid     The id of the object the hook is called for (module-specific).
     * @param array   $extrainfo  Extra information for the hook, dependent on hookaction.
     * @param boolean $implode    Implode collapses all display hooks into a single string.
     * @param object  $subject    Default $this controller.
     * @param array   $args       Extra arguments.
     *
     * @return string|array String output from GUI hooks, extrainfo array for API hooks.
     */
    public function callHooks($hookobject, $hookaction, $hookid, $extrainfo = array(), $implode = true, $subject = null, array $args = array())
    {
        if (is_null($subject)) {
            $subject = $this;
        }

        return ModUtil::callHooks($hookobject, $hookaction, $hookid, $extrainfo, $implode, $subject, $args);
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


}