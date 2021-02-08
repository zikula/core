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

namespace App\Hook;

use Zikula\Bundle\HookBundle\HookEvent\FilterHookEvent;

final class AppFilterHookEvent extends FilterHookEvent
{
    public function getTitle(): string
    {
        return 'App Filter Hook';
    }

    public function getInfo(): string
    {
        return 'App Filter Hook information. This event is fired in the TestHookController to filter a text example.';
    }
}
