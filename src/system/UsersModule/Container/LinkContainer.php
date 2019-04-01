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

namespace Zikula\UsersModule\Container;

use Symfony\Component\Routing\RouterInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Core\LinkContainer\LinkContainerInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\SettingsModule\Api\ApiInterface\LocaleApiInterface;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersModule\Constant as UsersConstant;

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
     * @var LocaleApiInterface
     */
    private $localeApi;

    public function __construct(
        TranslatorInterface $translator,
        RouterInterface $router,
        PermissionApiInterface $permissionApi,
        VariableApiInterface $variableApi,
        CurrentUserApiInterface $currentUserApi,
        LocaleApiInterface $localeApi
    ) {
        $this->translator = $translator;
        $this->router = $router;
        $this->permissionApi = $permissionApi;
        $this->variableApi = $variableApi;
        $this->currentUser = $currentUserApi;
        $this->localeApi = $localeApi;
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

        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_MODERATE)) {
            $links[] = [
                'url' => $this->router->generate('zikulausersmodule_useradministration_list'),
                'text' => $this->translator->__('Users list'),
                'icon' => 'list'
            ];
        }
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADMIN)) {
            $links[] = [
                'url' => $this->router->generate('zikulausersmodule_config_config'),
                'text' => $this->translator->__('Settings'),
                'icon' => 'wrench'
            ];
            $links[] = [
                'url' => $this->router->generate('zikulausersmodule_config_authenticationmethods'),
                'text' => $this->translator->__('Authentication methods'),
                'icon' => 'lock'
            ];
        }
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_MODERATE)) {
            $links[] = [
                'url' => $this->router->generate('zikulausersmodule_fileio_export'),
                'text' => $this->translator->__('Export users'),
                'icon' => 'download',
            ];
            $links[] = [
                'url' => $this->router->generate('zikulausersmodule_useradministration_search'),
                'text' => $this->translator->__('Find/Mail/Delete users'),
                'icon' => 'search'
            ];
        }

        return $links;
    }

    /**
     * Get the user links for this extension.
     */
    private function getUser(): array
    {
        $links = [];
        $links[] = [
            'icon' => 'fa fa-user-circle-o',
            'text' => $this->translator->__('Account menu'),
            'url' => $this->router->generate('zikulausersmodule_account_menu')
        ];

        if (!$this->currentUser->isLoggedIn()) {
            $links[] = [
                'icon' => 'sign-in',
                'text' => $this->translator->__('Log in'),
                'url' => $this->router->generate('zikulausersmodule_access_login')
            ];

            if ($this->variableApi->get($this->getBundleName(), UsersConstant::MODVAR_REGISTRATION_ENABLED)) {
                $links[] = [
                    'icon' => 'plus',
                    'text'  => $this->translator->__('New account'),
                    'url'   => $this->router->generate('zikulausersmodule_registration_register')
                ];
            }
        }

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

        if ($this->variableApi->getSystemVar('multilingual')) {
            $locales = $this->localeApi->getSupportedLocales();
            if (count($locales) > 1) {
                $links[] = [
                    'url'   => $this->router->generate('zikulausersmodule_account_changelanguage'),
                    'text' => $this->translator->__('Language switcher'),
                    'icon'  => 'language'
                ];
            }
        }

        $links[] = [
            'url'   => $this->router->generate('zikulausersmodule_access_logout'),
            'text' => $this->translator->__('Log out'),
            'icon'  => 'power-off text-danger'
        ];

        return $links;
    }

    public function getBundleName(): string
    {
        return 'ZikulaUsersModule';
    }
}
