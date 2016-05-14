<?php
/**
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
     * set the BundleName as required buy the interface
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

        if ($this->permissionApi->hasPermission("ZikulaUsersModule::", '::', ACCESS_MODERATE)) {
            $links[] = [
                'url' => $this->router->generate('zikulausersmodule_useradministration_list'),
                'text' => $this->translator->__('Users list'),
                'icon' => 'list'
            ];
        }
        if ($this->permissionApi->hasPermission("ZikulaUsersModule::", '::', ACCESS_MODERATE)) {
            $pending = $this->registrationHelper->countAll();
            if ($pending) {
                $links[] = [
                    'url' => $this->router->generate('zikulausersmodule_registrationadministration_list'),
                    'text' => $this->translator->__('Pending registrations') . ' (' . $pending . ')',
                    'icon' => 'plus'
                ];
            }
        }

        // To create a new user (or import users) when registration is enabled, ADD access is required.
        // If registration is disabled, then ADMIN access required.
        // ADMIN access is always required for exporting the users.
        if ($this->variableApi->get('ZikulaUsersModule', UsersConstant::MODVAR_REGISTRATION_ENABLED, false)) {
            $createUserAccessLevel = ACCESS_ADD;
        } else {
            $createUserAccessLevel = ACCESS_ADMIN;
        }
        if ($this->permissionApi->hasPermission("ZikulaUsersModule::", '::', $createUserAccessLevel)) {
            $submenulinks = [];
            $submenulinks[] = [
                'url' => $this->router->generate('zikulausersmodule_useradministration_create'),
                'text' => $this->translator->__('Create new user')
            ];
            $submenulinks[] = [
                'url' => $this->router->generate('zikulausersmodule_fileio_import'),
                'text' => $this->translator->__('Import users')
            ];
            if ($this->permissionApi->hasPermission("ZikulaUsersModule::", '::', ACCESS_ADMIN)) {
                $submenulinks[] = [
                    'url' => $this->router->generate('zikulausersmodule_fileio_export'),
                    'text' => $this->translator->__('Export users')
                ];
            }
            $links[] = [
                'url' => $this->router->generate('zikulausersmodule_admin_newuser'),
                'text' => $this->translator->__('Create new user'),
                'icon' => 'plus',
                'links' => $submenulinks
            ];
        }
        if ($this->permissionApi->hasPermission("ZikulaUsersModule::", '::', ACCESS_MODERATE)) {
            $links[] = [
                'url' => $this->router->generate('zikulausersmodule_useradministration_search'),
                'text' => $this->translator->__('Find users'),
                'icon' => 'search'
            ];
        }
        if ($this->permissionApi->hasPermission("ZikulaUsersModule::", '::', ACCESS_ADMIN)) {
            $links[] = [
                'url' => $this->router->generate('zikulausersmodule_config_config'),
                'text' => $this->translator->__('Settings'),
                'icon' => 'wrench'
            ];
        }

        return $links;
    }

    private function getUser()
    {
        $isLoggedIn = \UserUtil::isLoggedIn();

        $links = [];

        // we do not check permissions for guests here (see #1874)
        if (!$isLoggedIn/* && $this->permissionApi->hasPermission('ZikulaUsersModule' . '::', '::', ACCESS_READ)*/) {
            $links[] = [
                'icon' => 'sign-in',
                'text' => $this->translator->__('Log in'),
                'url' => $this->router->generate('zikulausersmodule_user_login')

            ];

            $links[] = [
                'icon' => 'key',
                'links' => [
                    [
                        'text' => $this->translator->__('Recover Lost User Name'),
                        'url' => $this->router->generate('zikulausersmodule_account_lostusername')
                    ],
                    [
                        'text' => $this->translator->__('Recover Lost Password'),
                        'url' => $this->router->generate('zikulausersmodule_account_lostpassword')
                    ],
                    [
                        'text' => $this->translator->__('Enter Password Recovery Code'),
                        'url' => $this->router->generate('zikulausersmodule_account_confirmationcode')
                    ]
                ],
                'text' => $this->translator->__('Recover account information or password'),
                'url' => $this->router->generate('zikulausersmodule_account_menu'),
            ];

            if ($this->variableApi->get('ZikulaUsersModule', 'reg_allowreg')) {
                $links[] = [
                    'icon' => 'plus',
                    'text'  => $this->translator->__('New account'),
                    'url'   => $this->router->generate('zikulausersmodule_registration_register')
                ];
            }
        }

        if ($isLoggedIn && $this->permissionApi->hasPermission('ZikulaUsersModule' . '::', '::', ACCESS_READ)) {
            $links[] = [
                'icon' => 'wrench',
                'text' => $this->translator->__('Account Settings'),
                'url' => $this->router->generate('zikulausersmodule_user_index')
            ];
        }

        return $links;
    }

    private function getAccount()
    {
        $links = [];
        if (!\UserUtil::isLoggedIn()) {
            return $links;
        }

        // Show change password action only if the account record contains a password, and the password is not the
        // special marker for an account created without a Users module authentication password.
        $pass = $this->currentUser->get('pass');
        if (!empty($pass) && ($pass != UsersConstant::PWD_NO_USERS_AUTHENTICATION)) {
            // show edit password link
            $links[1] = [
                'url'   => $this->router->generate('zikulausersmodule_user_changepassword'),
                'text' => $this->translator->__('Password changer'),
                'icon'  => 'key text-success'
            ];
        }

        // show edit email link if configured to manage email address
        if ($this->variableApi->get('ZikulaUsersModule', 'changeemail', true)) {
            $links[2] = [
                'url'   => $this->router->generate('zikulausersmodule_user_changeemail'),
                'text' => $this->translator->__('E-mail address manager'),
                'icon'  => 'at'
            ];
        }

        if ($this->variableApi->get(VariableApi::CONFIG, 'multilingual')) {
            if (count(\ZLanguage::getInstalledLanguages()) > 1) {
                $links[3] = [
                    'url'   => $this->router->generate('zikulausersmodule_user_changelang'),
                    'text' => $this->translator->__('Language switcher'),
                    'icon'  => 'language'
                ];
            }
        }

        $links[4] = [
            'url'   => $this->router->generate('zikulausersmodule_user_logout'),
            'text' => $this->translator->__('Log out'),
            'icon'  => 'power-off text-danger'
        ];

        return $links;
    }
}
