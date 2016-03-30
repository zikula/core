<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\UsersModule\Container;

use Symfony\Component\Routing\RouterInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Core\LinkContainer\LinkContainerInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\PermissionsModule\Api\PermissionApi;
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
     * @var PermissionApi
     */
    private $permissionApi;
    /**
     * @var VariableApi
     */
    private $variableApi;

    /**
     * constructor.
     *
     * @param TranslatorInterface $translator
     * @param RouterInterface $router
     * @param PermissionApi $permissionApi
     * @param VariableApi $variableApi
     */
    public function __construct(TranslatorInterface $translator, RouterInterface $router, PermissionApi $permissionApi, VariableApi $variableApi)
    {
        $this->translator = $translator;
        $this->router = $router;
        $this->permissionApi = $permissionApi;
        $this->variableApi = $variableApi;
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
                'url' => $this->router->generate('zikulausersmodule_admin_view'),
                'text' => $this->translator->__('Users list'),
                'icon' => 'list'
            ];
        }
        if ($this->permissionApi->hasPermission("ZikulaUsersModule::", '::', ACCESS_MODERATE)) {
            $pending = (int) \ModUtil::apiFunc('ZikulaUsersModule', 'registration', 'countAll');
            if ($pending) {
                $links[] = [
                    'url' => $this->router->generate('zikulausersmodule_admin_viewregistrations'),
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
                'url' => $this->router->generate('zikulausersmodule_admin_newuser'),
                'text' => $this->translator->__('Create new user')
            ];
            $submenulinks[] = [
                'url' => $this->router->generate('zikulausersmodule_admin_import'),
                'text' => $this->translator->__('Import users')
            ];
            if ($this->permissionApi->hasPermission("ZikulaUsersModule::", '::', ACCESS_ADMIN)) {
                $submenulinks[] = [
                    'url' => $this->router->generate('zikulausersmodule_admin_exporter'),
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
                'url' => $this->router->generate('zikulausersmodule_admin_search'),
                'text' => $this->translator->__('Find users'),
                'icon' => 'search'
            ];
        }
        if ($this->permissionApi->hasPermission('ZikulaUsersModule::MailUsers', '::', ACCESS_MODERATE)) {
            $links[] = [
                'url' => $this->router->generate('zikulausersmodule_admin_mailusers'),
                'text' => $this->translator->__('E-mail users'),
                'icon' => 'envelope'
            ];
        }
        if ($this->permissionApi->hasPermission("ZikulaUsersModule::", '::', ACCESS_ADMIN)) {
            $links[] = [
                'url' => $this->router->generate('zikulausersmodule_admin_config'),
                'text' => $this->translator->__('Settings'),
                'icon' => 'wrench'
            ];
        }

        return $links;
    }

    private function getUser()
    {
        $messageModule = $this->variableApi->get(VariableApi::CONFIG, 'messagemodule', '');
        $profileModule = $this->variableApi->get(VariableApi::CONFIG, 'profilemodule', '');
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
                        'url' => $this->router->generate('zikulausersmodule_user_lostuname')
                    ],
                    [
                        'text' => $this->translator->__('Recover Lost Password'),
                        'url' => $this->router->generate('zikulausersmodule_user_lostpassword')
                    ],
                    [
                        'text' => $this->translator->__('Enter Password Recovery Code'),
                        'url' => $this->router->generate('zikulausersmodule_user_lostpasswordcode')
                    ]
                ],
                'text' => $this->translator->__('Recover account information or password'),
                'url' => $this->router->generate('zikulausersmodule_user_lostpwduname'),
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

        if ($isLoggedIn && !empty($profileModule) && \ModUtil::available($profileModule) && $this->permissionApi->hasPermission($profileModule . '::', '::', ACCESS_READ)) {
            $links[] = [
                'text' => $this->translator->__('Profile'),
                'url' => \ModUtil::url($profileModule, 'user', 'view'),
                'icon' => 'user',
                'links' => [
                    [
                        'text' => $this->translator->__('Edit Profile'),
                        'url' => \ModUtil::url($profileModule, 'user', 'modify')
                    ],
                    [
                        'text' => $this->translator->__('Change Email Address'),
                        'url' => $this->router->generate('zikulausersmodule_user_changeemail')
                    ],
                    [
                        'text' => $this->translator->__('Change Password'),
                        'url' => $this->router->generate('zikulausersmodule_user_changepassword')
                    ]
                ]
            ];
        }

        if ($isLoggedIn && !empty($messageModule) && \ModUtil::available($messageModule) && $this->permissionApi->hasPermission($messageModule . '::', '::', ACCESS_READ)) {
            $links[] = [
                'icon' => 'envelope',
                'text' => $this->translator->__('Messages'),
                'url' => \ModUtil::url($messageModule, 'user', 'main')
            ];
        }

        if (!empty($profileModule) && \ModUtil::available($profileModule) && $this->permissionApi->hasPermission($profileModule . ':Members:', '::', ACCESS_READ)) {
            $links[9999] = [
                'icon' => 'list',
                'text' => $this->translator->__('Registered Users'),
                'url' => \ModUtil::url($profileModule, 'user', 'viewmembers')
            ];

            if ($this->permissionApi->hasPermission($profileModule . ':Members:recent', '::', ACCESS_READ)) {
                $links[9999]['links'][] = [
                    'text' => $this->translator->__f('Last %s Registered Users', $this->variableApi->get($profileModule, 'recentmembersitemsperpage')),
                    'url' => \ModUtil::url($profileModule, 'user', 'recentmembers')
                ];
            }

            if ($this->permissionApi->hasPermission($profileModule . ':Members:online', '::', ACCESS_READ)) {
                $links[9999]['links'][] = [
                    'text' => $this->translator->__('Users Online'),
                    'url' => \ModUtil::url($profileModule, 'user', 'online')
                ];
            }
        }

        return $links;
    }
}
