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
use Zikula\Bundle\HookBundle\HookEvent\FormHookEvent;
use Zikula\Bundle\HookBundle\HookEvent\HookEvent;
use Zikula\Bundle\HookBundle\HookEvent\PostValidationFormHookEvent;
use Zikula\Bundle\HookBundle\HookEventListener\FormHookEventListener;
use Zikula\Bundle\HookBundle\HookEventListener\PostValidationFormHookEventListener;

class AppFormHookEventListener extends FormHookEventListener
{
    public function getTitle(): string
    {
        return 'NumberField+';
    }

    public function getInfo(): string
    {
        return 'This listener adds and handles a number field to a form.';
    }

    public function preHandleExecute(FormHookEvent $event)
    {
        $event->formAdd('number', IntegerType::class)
            ->addTemplate('test_hook/hook_number.html.twig');
    }

    public function postSubmitExecute(FormHookEvent $event)
    {
        $event->setDisplay((string) $event->getFormData('number'));
    }
}
