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

use Zikula\Bundle\HookBundle\HookEvent\PostValidationFormHookEvent;

final class AppPostValidationFormHookEvent extends PostValidationFormHookEvent
{
    public function getTitle(): string
    {
        return 'App post validation Form Hook';
    }

    public function getInfo(): string
    {
        return 'App post validation Form Hook information. This event is fired in the TestHookController after the form is validated.';
    }
}
