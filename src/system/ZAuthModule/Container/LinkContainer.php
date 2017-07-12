<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ZAuthModule\Container;

use Symfony\Component\Routing\RouterInterface;
use Zikula\Common\Translator\TranslatorInterface;
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

    /**
     * constructor.
     *
     * @param TranslatorInterface $translator
     * @param RouterInterface $router
     * @param PermissionApiInterface $permissionApi
     * @param VariableApiInterface $variableApi
     * @param CurrentUserApiInterface $currentUserApi
     * @param AuthenticationMappingRepositoryInterface $mappingRepository
     */
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

    /**
     * get Links of any type for this extension
     * required by the interface
     *
     * @param string $type
     * @return array
     */
    public function getLinks($type = LinkContainerInterface::TYPE_ADMIN)
    {
        if (LinkContainerInterface::TYPE_ADMIN == $type) {
            return $this->getAdmin();
        }
        if (LinkContainerInterface::TYPE_ACCOUNT == $type) {
            return $this->getAccount();
        }
        if (LinkContainerInterface::TYPE_USER == $type) {
            return $this->getUser();
        }

        return [];
    }

    /**
     * get the Admin links for this extension
     *
     * @return array
     */
    private function getAdmin()
    {
        $links = [];
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADMIN)) {
            $links[] = [
                'url' => $this->router->generate('zikulazauthmodule_useradministration_list'),
                'text' => $this->translator->__('Users list'),
                'icon' => 'list'
            ];
        }
        if ($this->variableApi->get('ZikulaUsersModule', UsersConstant::MODVAR_REGISTRATION_ENABLED, false)) {
            $createUserAccessLevel = ACCESS_ADD;
        } else {
            $createUserAccessLevel = ACCESS_ADMIN;
        }
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', $createUserAccessLevel)) {
            $submenulinks[] = [
                'url' => $this->router->generate('zikulazauthmodule_useradministration_create'),
                'text' => $this->translator->__('Create new user'),
            ];
            $submenulinks[] = [
                'url' => $this->router->generate('zikulazauthmodule_fileio_import'),
                'text' => $this->translator->__('Import users')
            ];
            $links[] = [
                'url' => $this->router->generate('zikulazauthmodule_useradministration_create'),
                'text' => $this->translator->__('New users'),
                'icon' => 'plus',
                'links' => $submenulinks
            ];
        }
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADMIN)) {
            $links[] = [
                'url' => $this->router->generate('zikulazauthmodule_config_config'),
                'text' => $this->translator->__('Settings'),
                'icon' => 'wrench'
            ];
        }

        return $links;
    }

    private function getUser()
    {
        $links = $this->getAccount();
        array_unshift($links, [
            'url'   => $this->router->generate('zikulausersmodule_account_menu'),
            'text' => $this->translator->__('Account menu'),
            'icon'  => 'fa fa-user-circle-o'
        ]);
        $links[] = [
            'icon' => 'key',
            'links' => [
                [
                    'text' => $this->translator->__('Recover Lost User Name'),
                    'url' => $this->router->generate('zikulazauthmodule_account_lostusername')
                ],
                [
                    'text' => $this->translator->__('Recover Lost Password'),
                    'url' => $this->router->generate('zikulazauthmodule_account_lostpassword')
                ]
            ],
            'text' => $this->translator->__('Recover account information or password'),
            'url' => $this->router->generate('zikulausersmodule_account_menu'),
        ];

        return $links;
    }

    private function getAccount()
    {
        $links = [];
        if (!$this->currentUser->isLoggedIn()) {
            return $links;
        }

        $userMapping = $this->mappingRepository->findOneBy(['uid' => $this->currentUser->get('uid')]);
        if (isset($userMapping)) {
            $links[] = [
                'url'   => $this->router->generate('zikulazauthmodule_account_changepassword'),
                'text' => $this->translator->__('Change password'),
                'icon'  => 'key text-success'
            ];
            $links[] = [
                'url'   => $this->router->generate('zikulazauthmodule_account_changeemail'),
                'text' => $this->translator->__('Change e-mail address'),
                'icon'  => 'at'
            ];
        }

        return $links;
    }

    /**
     * set the BundleName as required by the interface
     *
     * @return string
     */
    public function getBundleName()
    {
        return 'ZikulaZAuthModule';
    }
}
