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

class UserManagementUiHooksSubscriber implements HookSubscriberInterface
{
    const EDIT_DISPLAY = 'users.ui_hooks.user.display_view';
    const EDIT_FORM = 'users.ui_hooks.user.form_edit';
    const EDIT_VALIDATE = 'users.ui_hooks.user.validate_edit';
    const EDIT_PROCESS = 'users.ui_hooks.user.process_edit';
    const DELETE_FORM = 'users.ui_hooks.user.form_delete';
    const DELETE_VALIDATE = 'users.ui_hooks.user.validate_delete';
    const DELETE_PROCESS = 'users.ui_hooks.user.process_delete';

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
        return $this->translator->__('User management hooks');
    }

    public function getEvents()
    {
        return [
            UiHooksCategory::TYPE_DISPLAY_VIEW => self::EDIT_DISPLAY,
            UiHooksCategory::TYPE_FORM_EDIT => self::EDIT_FORM,
            UiHooksCategory::TYPE_VALIDATE_EDIT => self::EDIT_VALIDATE,
            UiHooksCategory::TYPE_PROCESS_EDIT => self::EDIT_PROCESS,
            UiHooksCategory::TYPE_FORM_DELETE => self::DELETE_FORM,
            UiHooksCategory::TYPE_VALIDATE_DELETE => self::DELETE_VALIDATE,
            UiHooksCategory::TYPE_PROCESS_DELETE => self::DELETE_PROCESS,
        ];
    }
}
