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

namespace Zikula\GroupsModule\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\GroupsModule\Constant;
use Zikula\GroupsModule\Entity\GroupEntity;
use Zikula\GroupsModule\Entity\Repository\GroupApplicationRepository;
use Zikula\GroupsModule\Helper\CommonHelper;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;

class MenuBuilder
{
    use TranslatorTrait;

    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var CurrentUserApiInterface
     */
    private $currentUserApi;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var GroupApplicationRepository
     */
    private $groupApplicationRepository;

    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(
        TranslatorInterface $translator,
        FactoryInterface $factory,
        PermissionApiInterface $permissionApi,
        VariableApiInterface $variableApi,
        RequestStack $requestStack,
        CurrentUserApiInterface $currentUserApi,
        UserRepositoryInterface $userRepository,
        GroupApplicationRepository $groupApplicationRepository,
        RouterInterface $router
    ) {
        $this->setTranslator($translator);
        $this->factory = $factory;
        $this->permissionApi = $permissionApi;
        $this->variableApi = $variableApi;
        $this->requestStack = $requestStack;
        $this->currentUserApi = $currentUserApi;
        $this->userRepository = $userRepository;
        $this->groupApplicationRepository = $groupApplicationRepository;
        $this->router = $router;
    }

    public function createAdminMenu(array $options)
    {
        $defaultGroup = $this->variableApi->get('ZikulaGroupsModule', 'defaultgroup');
        /** @var GroupEntity $group */
        $group = $options['group'];
        $gid = $group->getGid();
        $routeParams = ['gid' => $gid];
        $menu = $this->factory->createItem('adminActions');
        $menu->setChildrenAttribute('class', 'list-inline');
        $menu->addChild($this->__f('Edit ":name" group', [':name' => $group->getName()]), [
            'route' => 'zikulagroupsmodule_group_edit',
            'routeParameters' => $routeParams,
        ])->setAttribute('icon', 'fa fa-pencil');
        if ($this->permissionApi->hasPermission('ZikulaGroupsModule::', $gid . '::', ACCESS_DELETE)
            && $gid !== $defaultGroup && Constant::GROUP_ID_ADMIN !== $gid) {
            $menu->addChild($this->__f('Delete ":name" group', [':name' => $group->getName()]), [
                'route' => 'zikulagroupsmodule_group_remove',
                'routeParameters' => $routeParams,
            ])->setAttribute('icon', 'fa fa-trash-o');
        }
        $menu->addChild($this->__('Group membership'), [
            'route' => 'zikulagroupsmodule_membership_adminlist',
            'routeParameters' => $routeParams,
        ])->setAttribute('icon', 'fa fa-users');

        return $menu;
    }

    public function createUserMenu(array $options)
    {
        /** @var GroupEntity $group */
        $group = $options['group'];
        $gid = $group->getGid();
        $menu = $this->factory->createItem('userActions');
        $menu->setChildrenAttribute('class', 'list-inline');
        $requestAttributes = $this->requestStack->getCurrentRequest()->attributes->all();
        $currentUserId = $this->currentUserApi->get('uid');
        if (null !== $currentUserId) {
            $currentUser = $this->userRepository->find($currentUserId);
        }

        if ($this->permissionApi->hasPermission('ZikulaGroupsModule::', $gid . '::', ACCESS_READ)
            && ('zikulagroupsmodule_membership_list' !== $requestAttributes['_route'])
            && (CommonHelper::GTYPE_PUBLIC === $group->getGtype()
                || (CommonHelper::GTYPE_PRIVATE === $group->getGtype() && isset($currentUser) && $group->getUsers()->contains($currentUser)))
        ) {
            $menu->addChild($this->__f('View membership of ":name" group', [':name' => $group->getName()]), [
                'route' => 'zikulagroupsmodule_membership_list',
                'routeParameters' => ['gid' => $gid],
            ])->setAttribute('icon', 'fa fa-users');
        }
        if (isset($currentUser)) {
            if ($group->getUsers()->contains($currentUser)) {
                $menu->addChild($this->__f('Leave ":name" group', [':name' => $group->getName()]), [
                    'route' => 'zikulagroupsmodule_membership_leave',
                    'routeParameters' => ['gid' => $gid],
                ])->setAttribute('icon', 'fa fa-user-times text-danger');
            } elseif (CommonHelper::GTYPE_PRIVATE === $group->getGtype()) {
                $existingApplication = $this->groupApplicationRepository->findOneBy(['group' => $group, 'user' => $currentUser]);
                if ($existingApplication) {
                    $menu->addChild($this->__('Applied!'));
                } else {
                    $menu->addChild($this->__f('Apply to membership of ":name" group', [':name' => $group->getName()]), [
                        'route' => 'zikulagroupsmodule_application_create',
                        'routeParameters' => ['gid' => $gid],
                    ])->setAttribute('icon', 'fa fa-paper-plane');
                }
            } elseif (CommonHelper::STATE_CLOSED !== $group->getState()) {
                $menu->addChild($this->__f('Join ":name" group', [':name' => $group->getName()]), [
                    'route' => 'zikulagroupsmodule_membership_join',
                    'routeParameters' => ['gid' => $gid],
                ])->setAttribute('icon', 'fa fa-user-plus text-success');
            }
        } else {
            $returnUrl = $this->router->generate('zikulagroupsmodule_membership_list', ['gid' => $gid], UrlGeneratorInterface::ABSOLUTE_URL);
            $menu->addChild($this->__('Log in or register'), [
                'route' => 'zikulausersmodule_access_login',
                'routeParameters' => ['returnUrl' => $returnUrl]
            ])->setAttribute('icon', 'fa fa-key');
        }

        return $menu;
    }

    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }
}
