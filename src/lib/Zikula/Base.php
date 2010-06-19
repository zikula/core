<?php
/**
 * Copyright 2010 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
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
    protected $name;
    protected $options;
    protected $baseDir;
    protected $systemBaseDir;
    protected $libBaseDir;
    protected $modinfo;
    protected $domain = null;
    protected $serviceManager;
    protected $eventManager;
    protected $reflection;

    public function __construct(Zikula_ServiceManager $serviceManager, Zikula_EventManager $eventManager, array $options = array())
    {
        $this->serviceManager = $serviceManager;
        $this->eventManager = $eventManager;
        $this->options = $options;
        $this->_setup();
        
        if ($this->modinfo['type'] == ModUtil::TYPE_MODULE) {
            $this->domain = ZLanguage::getModuleDomain($this->name);
        }

        $this->postInitialize();
    }

    private function _setup()
    {
        $this->reflection = new ReflectionObject($this);
        $parts = explode('_', $this->reflection->getName());
        $this->name = $parts[0];
        $this->modinfo = ModUtil::getInfo(ModUtil::getIdFromName($this->name));
        $modbase = ($this->modinfo['type'] == ModUtil::TYPE_MODULE) ? 'modules' : 'system';
        $this->systemBaseDir = realpath("$modbase/..");
        $this->baseDir = realpath("{$this->systemBaseDir}/$modbase/" . $this->modinfo['directory']);
        $this->libBaseDir = realpath("{$this->baseDir}/lib/" . $this->modinfo['directory']);
    }

    public function getReflection()
    {
        return $this->reflection;
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getModInfo()
    {
        return $this->modinfo;
    }

    public function getBaseDir()
    {
        return $this->baseDir();
    }

    public function getLibBaseDir()
    {
        return $this->libBaseDir;
    }

    public function getSystemBaseDir()
    {
        return $this->systemBaseDir;
    }

    public function __($msgid)
    {
        return __($msgid, $this->domain);
    }

    protected function __f($msgid, $params)
    {
        return __f($msgid, $params, $this->domain);
    }

    protected function _n($singular, $plural, $count)
    {
        return _n($singular, $plural, $count, $this->domain);
    }

    protected function _fn($sin, $plu, $n, $params)
    {
        return _fn($sin, $plu, $n, $params, $this->domain);
    }

    protected function postInitialize()
    {

    }

    protected function throwNotFound($message='', $code=0, $debug=null)
    {
        throw new Zikula_Exception_NotFound($message, $code, $debug);
    }

    protected function throwNotFoundIf($condition, $message='', $code=0, $debug=null)
    {
        if ($condition) {
            $this->throwNotFound($message, $code, $debug);
        }
    }

    protected function throwNotFoundUnless($condition, $message='', $code=0, $debug=null)
    {
        if (!$condition) {
            $this->throwNotFound($message, $code, $debug);
        }
    }

    protected function throwForbidden($message='', $code=0, $debug=null)
    {
        throw new Zikula_Exception_Forbidden($message, $code, $debug);
    }

    protected function throwForbiddenIf($condition, $message='', $code=0, $debug=null)
    {
        if ($condition) {
            $this->throwForbidden($message, $code, $debug);
        }
    }

    protected function throwForbiddenUnless($condition, $message='', $code=0, $debug=null)
    {
        if (!$condition) {
            $this->throwForbidden($message, $code, $debug);
        }
    }

    protected function redirect($url, $type = 302)
    {
        throw new Zikula_Exception_Redirect($url, $type);
    }

    protected function redirectIf($condition, $url, $type = 302)
    {
        if ($condition) {
            $this->redirect($url, $type);
        }
    }

    protected function redirectUnless($condition, $url, $type = 302)
    {
        if (!$condition) {
            $this->redirect($url, $type);
        }
    }
    
    protected function registerStatus($message)
    {
    	if (!isset($message) || empty($message)) {
    		throw new Zikula_Exception($this->__f('Empty [%s] received.', 'message'));
    	}
    	
    	$msgs = SessionUtil::getVar('_ZStatusMsg', array());
        
    	$msgs[] = DataUtil::formatForDisplayHTML((string) $message);
    	
        SessionUtil::setVar('_ZStatusMsg', $msgs);
        
        return $this;
    }
    
    protected function registerStatusIf($condition, $message)
    {
    	if ($condition) {
    		return $this->registerStatus($message);
    	}
    }
    
    protected function registerStatusUnless($condition, $message)
    {
        if (!$condition) {
            return $this->registerStatus($message);
        }
    }

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

        $message = DataUtil::formatForDisplayHTML((string) $message);        
        if (!$showDetailInfo) {
            $msg = $message;
        } else {
            // TODO A [do we need to have HTML sanitization] (drak)
            $func = ((!empty($class)) ? "$class::$func" : $func);
            $msg = __f('%1$s The origin of this message was \'%2$s\' at line %3$s in file \'%4$s\'.', array($message, $func, $line, $file));
            //
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
    
    protected function registerErrorIf($condition, $message, $type=null, $debug=null)
    {
        if ($condition) {
            return $this->registerError($message, $type, $debug);
        }
    }
    
    protected function registerErrorUnless($condition, $message, $type=null, $debug=null)
    {
        if (!$condition) {
            return $this->registerError($message, $type, $debug);
        }
    }

}