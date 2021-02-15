<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle\HookEventListener;

use Zikula\Bundle\HookBundle\HookEvent\HookEvent;

/**
 * A HookEventListener must be implemented for each HookEvent that you wish to
 * make a connection with in your applicaiton. You cannot combine Listeners.
 */
interface HookEventListenerInterface
{
    public const EXECUTE_METHOD = 'execute';

    /**
     * The abstract HookEvent classname to which this listener is responding.
     * The first concrete class should mark this method `final`
     */
    public function listensTo(): string;

    /**
     * A short title indicating the purpose of the subscriber.
     * It is recommended to inject TranslatorInterface in the constructor and translate this string.
     */
    public function getTitle(): string;

    /**
     * A long text providing further information about the subscriber.
     * It is recommended to inject TranslatorInterface in the constructor and translate this string.
     */
    public function getInfo(): string;

    /**
     * Take action based on the event. $event may modified.
     */
    public function execute(HookEvent $event): void;

    public function getClassname(): string;
}
