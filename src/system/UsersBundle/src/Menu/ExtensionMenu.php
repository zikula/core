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

namespace Zikula\UsersBundle\Menu;

use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use Symfony\Bundle\SecurityBundle\Security;
use Zikula\ThemeBundle\ExtensionMenu\AbstractExtensionMenu;
use Zikula\UsersBundle\Entity\Group;
use Zikula\UsersBundle\Entity\User;
use Zikula\UsersBundle\UsersConstant;
use function Symfony\Component\Translation\t;

class ExtensionMenu extends AbstractExtensionMenu
{
    public function __construct(
        private readonly Security $security,
        private readonly bool $registrationEnabled,
        private readonly bool $allowSelfDeletion
    ) {
    }

    protected function getAdmin(): iterable
    {
        yield MenuItem::linkToCrud(t('Users'), 'fas fa-user', User::class);
        yield MenuItem::linkToCrud(t('Groups'), 'fas fa-people-group', Group::class);
    }

    protected function getUser(): iterable
    {
        if (null === $this->security->getUser()) {
            yield MenuItem::linktoRoute(t('Login'), 'fas fa-sign-in-alt', 'nucleos_user_security_login');
            if ($this->registrationEnabled) {
                yield MenuItem::linktoRoute(t('New account'), 'fas fa-plus', 'nucleos_profile_registration_register');
            }
        }
    }

    protected function getAccount(): iterable
    {
        $loggedIn = null !== $this->security->getUser();
        if (!$loggedIn) {
            yield MenuItem::linktoRoute(t('I would like to login'), 'fas fa-sign-in-alt', 'nucleos_user_security_login');
            if ($this->registrationEnabled) {
                yield MenuItem::linktoRoute(t('I would like to create a new account'), 'fas fa-plus', 'nucleos_profile_registration_register');
            }
        }
        yield MenuItem::linktoRoute(t('Reset password'), 'fas fa-refresh', 'nucleos_user_resetting_request');
        if ($loggedIn) {
            yield MenuItem::linktoRoute(t('Update security'), 'fas fa-lock', 'nucleos_user_update_security');

            yield MenuItem::linktoRoute(t('My profile'), 'fas fa-user', 'nucleos_profile_profile_show');
            yield MenuItem::linktoRoute(t('Edit profile'), 'fas fa-user-pen', 'nucleos_profile_profile_edit');

            if ($this->allowSelfDeletion) {
                if (UsersConstant::USER_ID_ADMIN !== $this->security->getUser()->getId()) {
                    yield MenuItem::linktoRoute(t('Delete my account'), 'fas fa-trash-alt', 'nucleos_user_delete_account')
                        ->setCssClass('text-danger')
                    ;
                }
            }
        }
    }

    public function getBundleName(): string
    {
        return 'ZikulaUsersBundle';
    }
}
