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

namespace Zikula\UsersModule\HookSubscriber;

use Zikula\Bundle\HookBundle\Category\UiHooksCategory;
use Zikula\Bundle\HookBundle\HookSubscriberInterface;
use Zikula\Common\Translator\TranslatorInterface;

class LoginUiHooksSubscriber implements HookSubscriberInterface
{
    public const LOGIN_FORM = 'users.ui_hooks.login_screen.form_edit';

    public const LOGIN_VALIDATE = 'users.ui_hooks.login_screen.validate_edit';

    public const LOGIN_PROCESS = 'users.ui_hooks.login_screen.process_edit';

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
        return 'ZikulaUsersModule';
    }

    public function getCategory(): string
    {
        return UiHooksCategory::NAME;
    }

    public function getTitle(): string
    {
        return $this->translator->__('Login form and block hooks');
    }

    public function getAreaName(): string
    {
        return 'subscriber.users.ui_hooks.login_screen';
    }

    public function getEvents(): array
    {
        return [
            UiHooksCategory::TYPE_FORM_EDIT => self::LOGIN_FORM,
            UiHooksCategory::TYPE_VALIDATE_EDIT => self::LOGIN_VALIDATE,
            UiHooksCategory::TYPE_PROCESS_EDIT => self::LOGIN_PROCESS
        ];
    }
}
