<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\MailerModule\HookSubscriber;

use Zikula\Bundle\HookBundle\Category\UiHooksCategory;
use Zikula\Bundle\HookBundle\HookSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UiHooksSubscriber implements HookSubscriberInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getOwner(): string
    {
        return 'ZikulaMailerModule';
    }

    public function getCategory(): string
    {
        return UiHooksCategory::NAME;
    }

    public function getTitle(): string
    {
        return $this->translator->trans('HTML mail hook');
    }

    public function getAreaName(): string
    {
        return 'mailer.ui_hooks.htmlmail.form_edit';
    }

    public function getEvents(): array
    {
        return [
            UiHooksCategory::TYPE_FORM_EDIT => 'mailer.ui_hooks.htmlmail.form_edit'
        ];
    }
}
