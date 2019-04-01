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

class UserManagementUiHooksSubscriber implements HookSubscriberInterface
{
    public const EDIT_DISPLAY = 'users.ui_hooks.user.display_view';

    public const EDIT_FORM = 'users.ui_hooks.user.form_edit';

    public const EDIT_VALIDATE = 'users.ui_hooks.user.validate_edit';

    public const EDIT_PROCESS = 'users.ui_hooks.user.process_edit';

    public const DELETE_FORM = 'users.ui_hooks.user.form_delete';

    public const DELETE_VALIDATE = 'users.ui_hooks.user.validate_delete';

    public const DELETE_PROCESS = 'users.ui_hooks.user.process_delete';

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
        return $this->translator->__('User management hooks');
    }

    public function getAreaName(): string
    {
        return 'subscriber.users.ui_hooks.user';
    }

    public function getEvents(): array
    {
        return [
            UiHooksCategory::TYPE_DISPLAY_VIEW => self::EDIT_DISPLAY,
            UiHooksCategory::TYPE_FORM_EDIT => self::EDIT_FORM,
            UiHooksCategory::TYPE_VALIDATE_EDIT => self::EDIT_VALIDATE,
            UiHooksCategory::TYPE_PROCESS_EDIT => self::EDIT_PROCESS,
            UiHooksCategory::TYPE_FORM_DELETE => self::DELETE_FORM,
            UiHooksCategory::TYPE_VALIDATE_DELETE => self::DELETE_VALIDATE,
            UiHooksCategory::TYPE_PROCESS_DELETE => self::DELETE_PROCESS
        ];
    }
}
