<?php
/**
 * Copyright 2009 Zikula Foundation.
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
 * Custom Hook Handler interface.
 */
abstract class Zikula_Hook_AbstractHandler implements Zikula_TranslatableInterface
{
    /**
     * EventManager instance.
     *
     * @var Zikula_EventManager
     */
    protected $eventManager;

    /**
     * Translation domain.
     *
     * @var string
     */
    protected $domain;

    /**
     * Display hook response object.
     *
     * @var Zikula_Response_DisplayHook
     */
    protected $display;

    /**
     * Validation object.
     *
     * @var Zikula_Hook_ValidationResponse
     */
    protected $validation;

    /**
     * This object's reflection.
     *
     * @var ReflectionObject
     */
    protected $reflection;

    /**
     * Constructor.
     *
     * @param Zikula_EventManager $eventManager ServiceManager.
     *
     * @throws InvalidArgumentException If $this->eventNames is invalid.
     */
    public function __construct(Zikula_EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
        $this->setup();
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
     * Post constructor hook.
     *
     * Generally used to set the $domain property.
     *
     * @return void
     */
    public function setup()
    {
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
