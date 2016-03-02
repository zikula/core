<?php
/**
 * Copyright 2009 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage \Zikula\Core\Core
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\HookBundle\Hook;

use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher as EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Zikula_TranslatableInterface;

/**
 * Custom Hook Handler interface.
 */
abstract class AbstractHookListener implements Zikula_TranslatableInterface
{
    /**
     * Dispatcher instance.
     *
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * Translation domain.
     *
     * @var string
     */
    protected $domain;

    /**
     * Display hook response object.
     *
     * @var \Zikula\Bundle\HookBundle\Hook\DisplayHookResponse
     */
    protected $display;

    /**
     * Validation object.
     *
     * @var \Zikula\Bundle\HookBundle\Hook\ValidationResponse
     */
    protected $validation;

    /**
     * This object's reflection.
     *
     * @var \ReflectionObject
     */
    protected $reflection;

    /**
     * Constructor.
     *
     * @param EventDispatcher $dispatcher ServiceManager.
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
        $this->setup();
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
     * Get dispatcher.
     *
     * @return EventDispatcher
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
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
