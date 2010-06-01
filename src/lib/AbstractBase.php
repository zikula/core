<?php
/**
 * Copyright 2010 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option, any later version).
 * @package EventManager
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * AbstractBase class for module abstract controllers and apis.
 */
abstract class AbstractBase
{
    protected $name;
    protected $options;
    protected $baseDir;
    protected $modinfo;
    protected $domain;

    public function __construct(array $options = array())
    {
        $this->_setup();
        $this->options = $options;

        if ($this->modinfo['type'] == ModUtil::TYPE_MODULE) {
            ZLanguage::bindModuleDomain($modname);
            $this->domain = ZLanguage::getModuleDomain($this->name);
        }

        //EventManagerUtil::attachCustomHandlers(realpath($this->baseDir. '/EventHandlers'));

        $this->postInitialize();
    }

    private function _setup()
    {
        $r = new ReflectionObject($this);
        //$this->baseDir = substr($r->getFileName(), 0, strrpos($r->getFileName(), $r->getName().'.php')-1);
        $parts = explode('_', $r->getName());
        $this->name = $parts[0];
        $this->modinfo = ModUtil::getInfo(ModUtil::getIdFromName($this->name));
        $base = ($this->modinfo['type'] == ModUtil::TYPE_MODULE) ? 'module' : 'system';
        $this->baseDir = $base . DIRECTORY_SEPARATOR . $this->modinfo['directory'];
    }

    public function __($msgid)
    {
        return __($msgid, $this->domain);
    }

    protected function _f($msgid, $params)
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


}