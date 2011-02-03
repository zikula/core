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
 * Helper base class.
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
     * Options (universal constructor).
     *
     * @var array
     */
    protected $options;

    /**
     * Translation domain.
     *
     * @var string|null
     */
    protected $domain = null;

    /**
     * Constructor.
     *
     * @param Zikula_Base $base    Zikula base object.
     * @param array       $options Options (universal constructor).
     */
    public function __construct(Zikula_ServiceManager $serviceManager, array $options = array())
    {
        $this->serviceManager = $serviceManager;
        $this->eventManager = $serviceManager->getService('zikula.eventmanager');
        $this->options = $options;
        Zikula_ClassProperties::load($this, $options);
        $this->setup();
    }

    /**
     * Post constructor setup (invoked at construction).
     *
     * @return void
     */
    protected function setup()
    {
        
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
