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

namespace Zikula\UsersModule\HookSubscriber;

use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\HookBundle\Category\UiHooksCategory;
use Zikula\Bundle\HookBundle\HookSubscriberInterface;

class RegistrationUiHooksSubscriber implements HookSubscriberInterface
{
    public const REGISTRATION_DISPLAY = 'users.ui_hooks.registration.display_view';

    public const REGISTRATION_FORM = 'users.ui_hooks.registration.form_edit';

    public const REGISTRATION_VALIDATE = 'users.ui_hooks.registration.validate_edit';

    public const REGISTRATION_PROCESS = 'users.ui_hooks.registration.process_edit';

    public const REGISTRATION_DELETE_FORM = 'users.ui_hooks.registration.form_delete';

    public const REGISTRATION_DELETE_VALIDATE = 'users.ui_hooks.registration.validate_delete';

    public const REGISTRATION_DELETE_PROCESS = 'users.ui_hooks.registration.process_delete';

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
        return $this->translator->trans('Registration management hooks');
    }

    public function getAreaName(): string
    {
        return 'subscriber.users.ui_hooks.registration';
    }

    public function getEvents(): array
    {
        return [
            UiHooksCategory::TYPE_DISPLAY_VIEW => self::REGISTRATION_DISPLAY,
            UiHooksCategory::TYPE_FORM_EDIT => self::REGISTRATION_FORM,
            UiHooksCategory::TYPE_VALIDATE_EDIT => self::REGISTRATION_VALIDATE,
            UiHooksCategory::TYPE_PROCESS_EDIT => self::REGISTRATION_PROCESS,
            UiHooksCategory::TYPE_FORM_DELETE => self::REGISTRATION_DELETE_FORM,
            UiHooksCategory::TYPE_VALIDATE_DELETE => self::REGISTRATION_DELETE_VALIDATE,
            UiHooksCategory::TYPE_PROCESS_DELETE => self::REGISTRATION_DELETE_PROCESS
        ];
    }
}
