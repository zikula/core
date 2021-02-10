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

use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Zikula\Bundle\HookBundle\HookEvent\HookEvent;
use Zikula\Bundle\HookBundle\HookEvent\PreHandleRequestFormHookEvent;
use Zikula\Bundle\HookBundle\HookEventListener\PreHandleRequestFormHookEventListener;

class AppPreHandleRequestFormHookEventListener extends PreHandleRequestFormHookEventListener
{
    public function getTitle(): string
    {
        return 'Number Adder Listener';
    }

    public function getInfo(): string
    {
        return 'This listener adds a Number field to the form.';
    }

    public function execute(HookEvent $event): void
    {
        // Strongly encouraged to check for exact HookEvent type here because typehint required to be generic
        if ($event instanceof PreHandleRequestFormHookEvent) {
            $event->formAdd('number', IntegerType::class)
                ->addTemplate('test_hook/hook_number.html.twig');
        }
    }
}
