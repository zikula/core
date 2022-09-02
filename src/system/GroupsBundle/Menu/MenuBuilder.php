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

namespace Zikula\GroupsBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Zikula\GroupsBundle\Entity\Group;
use Zikula\GroupsBundle\GroupsConstant;
use Zikula\GroupsBundle\Helper\DefaultHelper;
use Zikula\PermissionsBundle\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersBundle\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersBundle\Repository\UserRepositoryInterface;

class MenuBuilder
{
    public function __construct(
        private readonly FactoryInterface $factory,
        private readonly PermissionApiInterface $permissionApi,
        private readonly RequestStack $requestStack,
        private readonly CurrentUserApiInterface $currentUserApi,
        private readonly UserRepositoryInterface $userRepository,
        private readonly RouterInterface $router,
        private readonly DefaultHelper $defaultHelper
    ) {
    }

    public function createAdminMenu(array $options): ItemInterface
    {
        /** @var Group $group */
        $group = $options['group'];
        $gid = $group->getGid();
        $routeParams = ['gid' => $gid];
        $menu = $this->factory->createItem('adminActions');
        $menu->setChildrenAttribute('class', 'list-inline');
        $menu->addChild('Edit group', [
            'route' => 'zikulagroupsbundle_group_edit',
            'routeParameters' => $routeParams,
        ])->setAttribute('icon', 'fas fa-pencil-alt');
        if (GroupsConstant::GROUP_ID_ADMIN !== $gid
            && $this->defaultHelper->getDefaultGroupId() !== $gid
            && $this->permissionApi->hasPermission('ZikulaGroupsBundle::', $gid . '::', ACCESS_DELETE)) {
            $menu->addChild('Delete group', [
                'route' => 'zikulagroupsbundle_group_remove',
                'routeParameters' => $routeParams,
            ])->setAttribute('icon', 'fas fa-trash-alt');
        }
        $menu->addChild('Group membership', [
            'route' => 'zikulagroupsbundle_membership_adminlist',
            'routeParameters' => $routeParams,
        ])->setAttribute('icon', 'fas fa-users');

        return $menu;
    }

    public function createUserMenu(array $options): ItemInterface
    {
        /** @var Group $group */
        $group = $options['group'];
        $gid = $group->getGid();
        $menu = $this->factory->createItem('userActions');
        $menu->setChildrenAttribute('class', 'list-inline');
        $requestAttributes = $this->requestStack->getCurrentRequest()->attributes->all();
        $currentUserId = $this->currentUserApi->get('uid');
        if (null !== $currentUserId) {
            $currentUser = $this->userRepository->find($currentUserId);
        }

        if ('zikulagroupsbundle_membership_list' !== $requestAttributes['_route']
            && $this->permissionApi->hasPermission('ZikulaGroupsBundle::', $gid . '::', ACCESS_READ)
            && (GroupsConstant::GTYPE_PUBLIC === $group->getGtype()
                || (GroupsConstant::GTYPE_PRIVATE === $group->getGtype() && isset($currentUser) && $group->getUsers()->contains($currentUser)))
        ) {
            $menu->addChild('View membership of group', [
                'route' => 'zikulagroupsbundle_membership_list',
                'routeParameters' => ['gid' => $gid],
            ])->setAttribute('icon', 'fas fa-users');
        }
        if (isset($currentUser)) {
            if ($group->getUsers()->contains($currentUser)) {
                $menu->addChild('Leave group', [
                    'route' => 'zikulagroupsbundle_membership_leave',
                    'routeParameters' => ['gid' => $gid],
                ])->setAttribute('icon', 'fas fa-user-times text-danger');
            // } elseif (GroupsConstant::GTYPE_PRIVATE === $group->getGtype()) {
            } elseif (GroupsConstant::STATE_CLOSED !== $group->getState()) {
                $menu->addChild('Join group', [
                    'route' => 'zikulagroupsbundle_membership_join',
                    'routeParameters' => ['gid' => $gid],
                ])->setAttribute('icon', 'fas fa-user-plus text-success');
            }
        } else {
            $returnUrl = $this->router->generate('zikulagroupsbundle_membership_list', ['gid' => $gid], UrlGeneratorInterface::ABSOLUTE_URL);
            $menu->addChild('Log in or register', [
                'route' => 'zikulausersbundle_access_login',
                'routeParameters' => ['returnUrl' => $returnUrl]
            ])->setAttribute('icon', 'fas fa-key');
        }

        return $menu;
    }
}
