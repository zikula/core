<?php
/**
 * Copyright 2010 Zikula Foundation
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
 * Helper class.
 */
abstract class Zikula_Helper implements Zikula_Translatable
{
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
     * Who we're helping.
     *
     * @var object
     */
    protected $object;

    /**
     * Translation domain.
     *
     * @var string|null
     */
    protected $domain = null;

    /**
     * This object's reflection.
     *
     * @var ReflectionObject
     */
    protected $reflection;

    /**
     * Setup of class.
     *
     * Generally helpers are instaciated with new Zikula_Helper($this), but it
     * will accept most Zikula classes, and can be customised with
     * customConstruct() method.  Ultimately, we need a Zikula_ServiceManager instance.
     *
     * Generally, helpers do not need to use translatable test, but if  required,
     * manually configure $this->domain in the configureDomain() to make
     * use of the Zikula_Translatable interface.
     *
     * @param object $object Zikula_Base, Zikula_ServiceManager, Zikula_EventHandler, Zikula_HookHandler, or other.
     */
    public function inject($object)
    {
        $this->helpee = $object;
        if ($object instanceof Zikula_Base || $object instanceof Zikula_EventHandler || $object instanceof Zikula_HookHandler) {
            $this->serviceManager = $object->getServiceManager();
            $this->eventManager = $this->serviceManager->getEventManager();
        } else if ($object instanceof Zikula_ServiceManager) {
            $this->serviceManager = $object;
            $this->eventManager = $this->serviceManager->getService('zikula.eventmanager');
        } else {
            $this->customInject($object);
        }
    }

    /**
     * Custom injector.
     *
     * @param object $subject Subject of this helper.
     *
     * @return void
     */
    protected function customInject($object)
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
     * Get eventManager.
     *
     * @return Zikula_EventManager
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * Get servicemanager.
     *
     * @return Zikula_ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Get translation domain property.
     *
     * @return string|null Domain.
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Set the translation domain property.
     *
     * @param string $domain Translation domain.
     *
     * @return void
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
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
}
