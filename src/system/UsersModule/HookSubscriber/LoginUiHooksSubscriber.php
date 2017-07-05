<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\HookSubscriber;

use Zikula\Bundle\HookBundle\Category\UiHooksCategory;
use Zikula\Bundle\HookBundle\HookSubscriberInterface;
use Zikula\Common\Translator\TranslatorInterface;

class LoginUiHooksSubscriber implements HookSubscriberInterface
{
    const LOGIN_FORM = 'users.ui_hooks.login_screen.form_edit';
    const LOGIN_VALIDATE = 'users.ui_hooks.login_screen.validate_edit';
    const LOGIN_PROCESS = 'users.ui_hooks.login_screen.process_edit';

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
        return 'ZikulaUsersModule';
    }

    public function getCategory()
    {
        return UiHooksCategory::NAME;
    }

    public function getTitle()
    {
        return $this->translator->__('Login form and block hooks');
    }

    public function getEvents()
    {
        return [
            UiHooksCategory::TYPE_FORM_EDIT => self::LOGIN_FORM,
            UiHooksCategory::TYPE_VALIDATE_EDIT => self::LOGIN_VALIDATE,
            UiHooksCategory::TYPE_PROCESS_EDIT => self::LOGIN_PROCESS,
        ];
    }
}
