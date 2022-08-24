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

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Zikula\MenuBundle\ExtensionMenu\ExtensionMenuInterface;
use Zikula\PermissionsBundle\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersBundle\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersBundle\Helper\RegistrationHelper;
use Zikula\UsersBundle\UsersConstant;
use Zikula\ZAuthBundle\Repository\AuthenticationMappingRepositoryInterface;

class ExtensionMenu implements ExtensionMenuInterface
{
    public function __construct(
        private readonly FactoryInterface $factory,
        private readonly PermissionApiInterface $permissionApi,
        private readonly CurrentUserApiInterface $currentUserApi,
        private readonly AuthenticationMappingRepositoryInterface $mappingRepository,
        private readonly RegistrationHelper $registrationHelper
    ) {
    }

    public function get(string $type = self::TYPE_ADMIN): ?ItemInterface
    {
        if (self::TYPE_ADMIN === $type) {
            return $this->getAdmin();
        }
        if (self::TYPE_ACCOUNT === $type) {
            return $this->getAccount();
        }

        return null;
    }

    private function getAdmin(): ?ItemInterface
    {
        $menu = $this->factory->createItem('zauthAdminMenu');
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADMIN)) {
            $menu->addChild('Users list', [
                'route' => 'zikulazauthbundle_useradministration_listmappings',
            ])->setAttribute('icon', 'fas fa-list');
        }
        $createUserAccessLevel = $this->registrationHelper->isRegistrationEnabled() ? ACCESS_ADD : ACCESS_ADMIN;
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', $createUserAccessLevel)) {
            $menu->addChild('New users', [
                'route' => 'zikulazauthbundle_useradministration_create',
            ])->setAttribute('icon', 'fas fa-plus')
                ->setAttribute('dropdown', true);
            $menu['New users']->addChild('Create new user', [
                'route' => 'zikulazauthbundle_useradministration_create',
            ]);
            $menu['New users']->addChild('Import users', [
                'route' => 'zikulazauthbundle_fileio_import',
            ]);
        }
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADMIN)) {
            $menu->addChild('Batch password change', [
                'route' => 'zikulazauthbundle_useradministration_batchforcepasswordchange',
            ])->setAttribute('icon', 'fas fa-lock');
        }

        return 0 === $menu->count() ? null : $menu;
    }

    private function getAccount(): ?ItemInterface
    {
        $menu = $this->factory->createItem('zauthAccountMenu');
        if (!$this->currentUser->isLoggedIn()) {
            $menu->addChild('UserName', [
                'label' => 'I have forgotten my account information (for example, my user name)',
                'route' => 'zikulazauthbundle_account_lostusername',
            ])->setAttribute('icon', 'fas fa-user');
            $menu->addChild('Password', [
                'label' => 'I have forgotten my password',
                'route' => 'zikulazauthbundle_account_lostpassword',
            ])->setAttribute('icon', 'fas fa-key');
        } else {
            $userMapping = $this->mappingRepository->findOneBy(['uid' => $this->currentUser->get('uid')]);
            if (isset($userMapping)) {
                $menu->addChild('Change password', [
                    'route' => 'zikulazauthbundle_account_changepassword',
                ])->setAttribute('icon', 'fas fa-key')
                    ->setLinkAttribute('class', 'text-success');
                $menu->addChild('Change e-mail address', [
                    'route' => 'zikulazauthbundle_account_changeemail',
                ])->setAttribute('icon', 'fas fa-at');
            }
        }

        return 0 === $menu->count() ? null : $menu;
    }

    public function getBundleName(): string
    {
        return 'ZikulaZAuthBundle';
    }
}
