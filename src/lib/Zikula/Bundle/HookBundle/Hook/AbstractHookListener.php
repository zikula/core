<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle\Hook;

use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher as EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Zikula\Common\Translator\TranslatorTrait;

/**
 * Custom Hook Handler interface.
 */
abstract class AbstractHookListener
{
    use TranslatorTrait;

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
     * @param EventDispatcher $dispatcher ServiceManager
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
        $this->setTranslator($dispatcher->getContainer()->get('translator.default'));
        $this->setup();
        if (null !== $this->domain) {
            $this->translator->setDomain($this->domain);
        }
    }

    public function setTranslator($translator)
    {
        $this->translator = $translator;
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
     * @return void
     */
    public function setup()
    {
    }
}
