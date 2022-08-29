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

namespace Zikula\ZAuthBundle\Menu;

use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use Zikula\PermissionsBundle\Api\ApiInterface\PermissionApiInterface;
use Zikula\ThemeBundle\ExtensionMenu\AbstractExtensionMenu;
use Zikula\UsersBundle\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersBundle\Helper\RegistrationHelper;
use Zikula\ZAuthBundle\Repository\AuthenticationMappingRepositoryInterface;

class ExtensionMenu extends AbstractExtensionMenu
{
    public function __construct(
        protected readonly PermissionApiInterface $permissionApi,
        private readonly CurrentUserApiInterface $currentUserApi,
        private readonly AuthenticationMappingRepositoryInterface $mappingRepository,
        private readonly RegistrationHelper $registrationHelper
    ) {
    }

    protected function getAdmin(): iterable
    {
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADMIN)) {
            yield MenuItem::linktoRoute('Users list', 'fas fa-list', 'zikulazauthbundle_useradministration_listmappings');
        }
        $createUserAccessLevel = $this->registrationHelper->isRegistrationEnabled() ? ACCESS_ADD : ACCESS_ADMIN;
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', $createUserAccessLevel)) {
            yield MenuItem::linktoRoute('Create new user', 'fas fa-plus', 'zikulazauthbundle_useradministration_create');
            yield MenuItem::linktoRoute('Import users', 'fas fa-upload', 'zikulazauthbundle_fileio_import');
        }
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADMIN)) {
            yield MenuItem::linktoRoute('Batch password change', 'fas fa-lock', 'zikulazauthbundle_useradministration_batchforcepasswordchange');
        }
    }

    protected function getAccount(): iterable
    {
        if (!$this->currentUserApi->isLoggedIn()) {
            yield MenuItem::linktoRoute('I have forgotten my account information (for example, my user name)', 'fas fa-user', 'zikulazauthbundle_account_lostusername');
            yield MenuItem::linktoRoute('I have forgotten my password', 'fas fa-key', 'zikulazauthbundle_account_lostpassword');
        } else {
            $userMapping = $this->mappingRepository->findOneBy(['uid' => $this->currentUserApi->get('uid')]);
            if (isset($userMapping)) {
                yield MenuItem::linktoRoute('Change password', 'fas fa-key', 'zikulazauthbundle_account_changepassword')
                    ->setCssClass('text-success')
                ;
                yield MenuItem::linktoRoute('Change e-mail address', 'fas fa-at', 'zikulazauthbundle_account_changeemail');
            }
        }
    }

    public function getBundleName(): string
    {
        return 'ZikulaZAuthBundle';
    }
}
