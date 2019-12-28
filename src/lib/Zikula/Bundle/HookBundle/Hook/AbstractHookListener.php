<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle\Hook;

use ReflectionObject;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Zikula\Common\Translator\TranslatorInterface;
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
     * @var EventDispatcherInterface
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
     * @var DisplayHookResponse
     */
    protected $display;

    /**
     * Validation object.
     *
     * @var ValidationResponse
     */
    protected $validation;

    /**
     * This object's reflection.
     *
     * @var ReflectionObject
     */
    protected $reflection;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
        $this->setTranslator($dispatcher->getContainer()->get('translator.default'));
        $this->setup();
        if (null !== $this->domain) {
            $this->translator->setDomain($this->domain);
        }
    }

    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    /**
     * Get reflection of this object.
     */
    public function getReflection(): ReflectionObject
    {
        if (!$this->reflection) {
            $this->reflection = new ReflectionObject($this);
        }

        return $this->reflection;
    }

    public function getDispatcher(): EventDispatcherInterface
    {
        return $this->dispatcher;
    }

    public function setup(): void
    {
    }
}
