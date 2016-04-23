<?php
/**
 * Copyright Zikula Foundation 2016 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv3 (or at your option any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\UsersModule\Twig\Extension\SimpleFunction;

use Symfony\Component\Routing\RouterInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\PermissionsModule\Api\PermissionApi;
use Zikula\UsersModule\Api\CurrentUserApi;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\UsersModule\Constant as UsersConstant;

class AdministrationActionsFunction
{
    /**
     * @var PermissionApi
     */
    private $permissionsApi;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var CurrentUserApi
     */
    private $currentUser;

    /**
     * AdministrationActionsFunction constructor.
     * @param $permissionsApi
     * @param $router
     */
    public function __construct(PermissionApi $permissionsApi, RouterInterface $router, TranslatorInterface $translator, CurrentUserApi $currentUserApi)
    {
        $this->permissionsApi = $permissionsApi;
        $this->router = $router;
        $this->translator = $translator;
        $this->currentUser = $currentUserApi;
    }

    /**
     * @param UserEntity $user
     * @return string
     */
    public function display(UserEntity $user)
    {
        $content = '';
        if (!$this->permissionsApi->hasPermission('ZikulaUsersModule::', 'ANY', ACCESS_MODERATE)) {
            return $content;
        }
        $hasModeratePermissionToUser = $this->permissionsApi->hasPermission('ZikulaUsersModule::', $user->getUname() . '::' . $user->getUid(), ACCESS_MODERATE);
        $hasEditPermissionToUser = $this->permissionsApi->hasPermission('ZikulaUsersModule::', $user->getUname() . '::' . $user->getUid(), ACCESS_EDIT);
        $hasDeletePermissionToUser = $this->permissionsApi->hasPermission('ZikulaUsersModule::', $user->getUname() . '::' . $user->getUid(), ACCESS_DELETE);
        $userHasActualPassword = (null != $user->getPass()) && ('' != $user->getPass()) && ($user->getPass() != UsersConstant::PWD_NO_USERS_AUTHENTICATION);
        if ($user->getUid() > 1 && $hasModeratePermissionToUser) {
            $url = $this->router->generate('zikulausersmodule_admin_lostusername', ['userid' => $user->getUid()]);
            $title = $this->translator->__f('Send user name to %sub%', ["%sub%" => $user->getUname()]);
            $content .= '<a class="fa fa-fw fa-user tooltips" href="' . $url . '" title="' . $title . '"></a>';
        } else {
            $content .= '<span class="fa-fw"></span>';
        }
        if ($userHasActualPassword && $hasModeratePermissionToUser) {
            $url = $this->router->generate('zikulausersmodule_admin_lostpassword', ['userid' => $user->getUid()]);
            $title = $this->translator->__f('Send password recovery code to %sub%', ["%sub%" => $user->getUname()]);
            $content .= '<a class="fa fa-fw fa-key tooltips" href="' . $url . '" title="' . $title . '"></a>';
        } else {
            $content .= '<span class="fa-fw"></span>';
        }
        if ($userHasActualPassword && $hasEditPermissionToUser) {
            if ($user->getAttributes()->containsKey('_Users_mustChangePassword') && $user->getAttributeValue('_Users_mustChangePassword')) {
                $title = $this->translator->__f('Cancel required change of password for %sub%', ["%sub%" => $user->getUname()]);
                $fa = 'unlock-alt';
            } else {
                $title = $this->translator->__f('Require %sub% to change password at next login', ["%sub%" => $user->getUname()]);
                $fa = 'lock';
            }
            $url = $this->router->generate('zikulausersmodule_admin_toggleforcedpasswordchange', ['userid' => $user->getUid()]);
            $content .= '<a class="fa fa-fw fa-' . $fa . ' tooltips" href="' . $url . '" title="' . $title . '"></a>';
        } else {
            $content .= '<span class="fa-fw"></span>';
        }
        if ($user->getUid() > 1 && $hasEditPermissionToUser) {
            $url = $this->router->generate('zikulausersmodule_useradministration_modify', ['user' => $user->getUid()]);
            $title = $this->translator->__f('Edit %sub%', ["%sub%" => $user->getUname()]);
            $content .= '<a class="fa fa-fw fa-pencil tooltips" href="' . $url . '" title="' . $title . '"></a>';
        } else {
            $content .= '<span class="fa-fw"></span>';
        }
        $isCurrentUser = $this->currentUser->get('uid') == $user->getUid();
        if ($user->getUid() > 2 && !$isCurrentUser && $hasDeletePermissionToUser) {
            $url = $this->router->generate('zikulausersmodule_useradministration_modify', ['user' => $user->getUid()]);
            $title = $this->translator->__f('Delete %sub%', ["%sub%" => $user->getUname()]);
            $content .= '<a class="fa fa-fw fa-trash-o tooltips" href="' . $url . '" title="' . $title . '"></a>';
        } else {
            $content .= '<span class="fa-fw"></span>';
        }

        return $content;
    }
}
