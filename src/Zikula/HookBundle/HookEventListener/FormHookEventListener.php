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

use Zikula\Bundle\HookBundle\HookEvent\FormHookEvent;
use Zikula\Bundle\HookBundle\HookEvent\HookEvent;

abstract class FormHookEventListener implements HookEventListenerInterface
{
    final public function listensTo(): string
    {
        return FormHookEvent::class;
    }

    final public function getClassname(): string
    {
        return static::class;
    }

    final public function execute(HookEvent $event): void
    {
        // Strongly encouraged to check for exact HookEvent type here because typehint required to be generic
        if ($event instanceof FormHookEvent) {
            if ($event->formIsSubmitted()) {
                $this->postSubmitExecute($event);
            } else {
                $this->preHandleExecute($event);
            }
        }
    }

    abstract public function preHandleExecute(FormHookEvent $event);

    abstract public function postSubmitExecute(FormHookEvent $event);
}
