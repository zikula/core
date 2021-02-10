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

namespace App\HookEvent;

use Zikula\Bundle\HookBundle\HookEvent\PreHandleRequestFormHookEvent;

final class AppPreHandleRequestFormHookEvent extends PreHandleRequestFormHookEvent
{
    public function getTitle(): string
    {
        return 'App Pre HandleRequest Hook';
    }

    public function getInfo(): string
    {
        return 'App Pre HandleRequest Hook information. This event is fired in the TestHookController before the form is handled.';
    }
}
