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

class RegistrationUiHooksSubscriber implements HookSubscriberInterface
{
    const REGISTRATION_DISPLAY = 'users.ui_hooks.registration.display_view';
    const REGISTRATION_FORM = 'users.ui_hooks.registration.form_edit';
    const REGISTRATION_VALIDATE = 'users.ui_hooks.registration.validate_edit';
    const REGISTRATION_PROCESS = 'users.ui_hooks.registration.process_edit';
    const REGISTRATION_DELETE_FORM = 'users.ui_hooks.registration.form_delete';
    const REGISTRATION_DELETE_VALIDATE = 'users.ui_hooks.registration.validate_delete';
    const REGISTRATION_DELETE_PROCESS = 'users.ui_hooks.registration.process_delete';

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
        return $this->translator->__('Registration management hooks');
    }

    public function getEvents()
    {
        return [
            UiHooksCategory::TYPE_DISPLAY_VIEW => self::REGISTRATION_DISPLAY,
            UiHooksCategory::TYPE_FORM_EDIT => self::REGISTRATION_FORM,
            UiHooksCategory::TYPE_VALIDATE_EDIT => self::REGISTRATION_VALIDATE,
            UiHooksCategory::TYPE_PROCESS_EDIT => self::REGISTRATION_PROCESS,
            UiHooksCategory::TYPE_FORM_DELETE => self::REGISTRATION_DELETE_FORM,
            UiHooksCategory::TYPE_VALIDATE_DELETE => self::REGISTRATION_DELETE_VALIDATE,
            UiHooksCategory::TYPE_PROCESS_DELETE => self::REGISTRATION_DELETE_PROCESS,
        ];
    }
}
