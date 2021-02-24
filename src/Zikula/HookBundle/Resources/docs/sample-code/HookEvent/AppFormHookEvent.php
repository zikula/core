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

use Zikula\Bundle\HookBundle\HookEvent\FormHookEvent;

final class AppFormHookEvent extends FormHookEvent
{
    public function getTitle(): string
    {
        return 'App Form Hook Event';
    }

    public function getInfo(): string
    {
        return 'App Form Hook Event information. This event is fired in the TestHookController with the TestType Form.';
    }
}
