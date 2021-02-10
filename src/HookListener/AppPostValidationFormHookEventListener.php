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

namespace App\HookListener;

use Zikula\Bundle\HookBundle\HookEvent\HookEvent;
use Zikula\Bundle\HookBundle\HookEvent\PostValidationFormHookEvent;
use Zikula\Bundle\HookBundle\HookEventListener\PostValidationFormHookEventListener;

class AppPostValidationFormHookEventListener extends PostValidationFormHookEventListener
{
    public function getTitle(): string
    {
        return 'Number Handler';
    }

    public function getInfo(): string
    {
        return 'This listener handles the number field that was added by AppPreHandleRequestFormHookEventListener';
    }

    public function execute(HookEvent $event): void
    {
        // Strongly encouraged to check for exact HookEvent type here because typehint required to be generic
        if ($event instanceof PostValidationFormHookEvent) {
             $event->setDisplay((string) $event->getFormData('number'));
        }
    }
}
