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

use Zikula\Bundle\HookBundle\HookEvent\PostValidationFormHookEvent;

abstract class PostValidationFormHookEventListener implements HookEventListenerInterface
{
    final public function listensTo(): string
    {
        return PostValidationFormHookEvent::class;
    }

    public function getClassname(): string
    {
        return static::class;
    }
}
