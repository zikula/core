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

namespace Zikula\ZAuthModule\Container;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Core\LinkContainer\LinkContainerInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\ZAuthModule\Entity\RepositoryInterface\AuthenticationMappingRepositoryInterface;

class LinkContainer implements LinkContainerInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var CurrentUserApiInterface
     */
    private $currentUser;

    /**
     * @var AuthenticationMappingRepositoryInterface
     */
    private $mappingRepository;

    public function __construct(
        TranslatorInterface $translator,
        RouterInterface $router,
        PermissionApiInterface $permissionApi,
        VariableApiInterface $variableApi,
        CurrentUserApiInterface $currentUserApi,
        AuthenticationMappingRepositoryInterface $mappingRepository
    ) {
        $this->translator = $translator;
        $this->router = $router;
        $this->permissionApi = $permissionApi;
        $this->variableApi = $variableApi;
        $this->currentUser = $currentUserApi;
        $this->mappingRepository = $mappingRepository;
    }

    public function getLinks(string $type = LinkContainerInterface::TYPE_ADMIN): array
    {
        if (LinkContainerInterface::TYPE_ADMIN === $type) {
            return $this->getAdmin();
        }
        if (LinkContainerInterface::TYPE_ACCOUNT === $type) {
            return $this->getAccount();
        }
        if (LinkContainerInterface::TYPE_USER === $type) {
            return $this->getUser();
        }

        return [];
    }

    /**
     * Get the admin links for this extension.
     */
    private function getAdmin(): array
    {
        $links = [];
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADMIN)) {
            $links[] = [
                'url' => $this->router->generate('zikulazauthmodule_useradministration_list'),
                'text' => $this->translator->trans('Users list'),
                'icon' => 'list'
            ];
        }
        if ($this->variableApi->get('ZikulaUsersModule', UsersConstant::MODVAR_REGISTRATION_ENABLED)) {
            $createUserAccessLevel = ACCESS_ADD;
        } else {
            $createUserAccessLevel = ACCESS_ADMIN;
        }
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', $createUserAccessLevel)) {
            $submenulinks[] = [
                'url' => $this->router->generate('zikulazauthmodule_useradministration_create'),
                'text' => $this->translator->trans('Create new user'),
            ];
            $submenulinks[] = [
                'url' => $this->router->generate('zikulazauthmodule_fileio_import'),
                'text' => $this->translator->trans('Import users')
            ];
            $links[] = [
                'url' => $this->router->generate('zikulazauthmodule_useradministration_create'),
                'text' => $this->translator->trans('New users'),
                'icon' => 'plus',
                'links' => $submenulinks
            ];
        }
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADMIN)) {
            $links[] = [
                'url' => $this->router->generate('zikulazauthmodule_config_config'),
                'text' => $this->translator->trans('Settings'),
                'icon' => 'wrench'
            ];
        }

        return $links;
    }

    /**
     * Get the user links for this extension.
     */
    private function getUser(): array
    {
        $links = $this->getAccount();
        array_unshift($links, [
            'url'   => $this->router->generate('zikulausersmodule_account_menu'),
            'text' => $this->translator->trans('Account menu'),
            'icon'  => 'fa fa-user-circle'
        ]);
        $links[] = [
            'icon' => 'key',
            'links' => [
                [
                    'text' => $this->translator->trans('Recover Lost User Name'),
                    'url' => $this->router->generate('zikulazauthmodule_account_lostusername')
                ],
                [
                    'text' => $this->translator->trans('Recover Lost Password'),
                    'url' => $this->router->generate('zikulazauthmodule_account_lostpassword')
                ]
            ],
            'text' => $this->translator->trans('Recover account information or password'),
            'url' => $this->router->generate('zikulausersmodule_account_menu'),
        ];

        return $links;
    }

    /**
     * Get the account links for this extension.
     */
    private function getAccount(): array
    {
        $links = [];
        if (!$this->currentUser->isLoggedIn()) {
            return $links;
        }

        $userMapping = $this->mappingRepository->findOneBy(['uid' => $this->currentUser->get('uid')]);
        if (isset($userMapping)) {
            $links[] = [
                'url'   => $this->router->generate('zikulazauthmodule_account_changepassword'),
                'text' => $this->translator->trans('Change password'),
                'icon'  => 'key text-success'
            ];
            $links[] = [
                'url'   => $this->router->generate('zikulazauthmodule_account_changeemail'),
                'text' => $this->translator->trans('Change e-mail address'),
                'icon'  => 'at'
            ];
        }

        return $links;
    }

    public function getBundleName(): string
    {
        return 'ZikulaZAuthModule';
    }
}
