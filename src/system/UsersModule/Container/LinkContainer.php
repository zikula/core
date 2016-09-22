<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Container;

use Symfony\Component\Routing\RouterInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Core\LinkContainer\LinkContainerInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\PermissionsModule\Api\PermissionApi;
use Zikula\UsersModule\Api\CurrentUserApi;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Helper\RegistrationHelper;

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
     * @var PermissionApi
     */
    private $permissionApi;

    /**
     * @var VariableApi
     */
    private $variableApi;

    /**
     * @var RegistrationHelper
     */
    private $registrationHelper;

    /**
     * @var CurrentUserApi
     */
    private $currentUser;

    /**
     * constructor.
     *
     * @param TranslatorInterface $translator
     * @param RouterInterface $router
     * @param PermissionApi $permissionApi
     * @param VariableApi $variableApi
     * @param RegistrationHelper $registrationHelper
     * @param CurrentUserApi $currentUserApi
     */
    public function __construct(
        TranslatorInterface $translator,
        RouterInterface $router,
        PermissionApi $permissionApi,
        VariableApi $variableApi,
        RegistrationHelper $registrationHelper,
        CurrentUserApi $currentUserApi
    ) {
        $this->translator = $translator;
        $this->router = $router;
        $this->permissionApi = $permissionApi;
        $this->variableApi = $variableApi;
        $this->registrationHelper = $registrationHelper;
        $this->currentUser = $currentUserApi;
    }

    /**
     * set the BundleName as required by the interface
     *
     * @return string
     */
    public function getBundleName()
    {
        return 'ZikulaUsersModule';
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
        $method = 'get' . ucfirst(strtolower($type));
        if (method_exists($this, $method)) {
            return $this->$method();
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

    private function getUser()
    {
        $isLoggedIn = \UserUtil::isLoggedIn();

        $links = [];
        $links[] = [
            'icon' => 'wrench',
            'text' => $this->translator->__('Account Settings'),
            'url' => $this->router->generate('zikulausersmodule_account_menu')
        ];

        if (!$isLoggedIn) {
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

    private function getAccount()
    {
        $links = [];
        if (!$this->currentUser->isLoggedIn()) {
            return $links;
        }

        if ($this->variableApi->getSystemVar('multilingual')) {
            if (count(\ZLanguage::getInstalledLanguages()) > 1) {
                $links[] = [
                    'url'   => $this->router->generate('zikulausersmodule_account_changelanguage'),
                    'text' => $this->translator->__('Language switcher'),
                    'icon'  => 'language'
                ];
            }
        }

        $links[] = [
            'url'   => $this->router->generate('zikulausersmodule_user_logout'),
            'text' => $this->translator->__('Log out'),
            'icon'  => 'power-off text-danger'
        ];

        return $links;
    }
}
