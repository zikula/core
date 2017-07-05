<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\MailerModule\HookSubscriber;

use Zikula\Bundle\HookBundle\Category\UiHooksCategory;
use Zikula\Bundle\HookBundle\HookSubscriberInterface;
use Zikula\Common\Translator\TranslatorInterface;

class UiHooksSubscriber implements HookSubscriberInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getOwner()
    {
        return 'ZikulaMailerModule';
    }

    public function getCategory()
    {
        return UiHooksCategory::NAME;
    }

    public function getTitle()
    {
        return $this->translator->__('HTML mail hook');
    }

    public function getEvents()
    {
        return [
            UiHooksCategory::TYPE_FORM_EDIT => 'mailer.ui_hooks.htmlmail.form_edit'
        ];
    }
}
