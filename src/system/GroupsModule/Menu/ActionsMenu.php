<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\GroupsModule\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\GroupsModule\Entity\GroupEntity;
use Zikula\GroupsModule\Helper\CommonHelper;

class ActionsMenu implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    use TranslatorTrait;

    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    public function adminMenu(FactoryInterface $factory, array $options)
    {
        $this->setTranslator($this->container->get('translator.default'));
        $permissionApi = $this->container->get('zikula_permissions_module.api.permission');
        $defaultGroup = $this->container->get('zikula_extensions_module.api.variable')->get('ZikulaGroupsModule', 'defaultgroup');
        $primaryAdminGroup = $this->container->get('zikula_extensions_module.api.variable')->get('ZikulaGroupsModule', 'primaryadmingroup', 2);
        /** @var GroupEntity $group */
        $group = $options['group'];
        $gid = $group->getGid();
        $routeParams = ['gid' => $gid];
        $menu = $factory->createItem('adminActions');
        $menu->setChildrenAttribute('class', 'list-inline');
        $menu->addChild($this->__f('Edit ":name" group', [':name' => $group->getName()]), [
            'route' => 'zikulagroupsmodule_group_edit',
            'routeParameters' => $routeParams,
        ])->setAttribute('icon', 'fa fa-pencil');
        if ($permissionApi->hasPermission('ZikulaGroupsModule::', $gid . '::', ACCESS_DELETE)
            && $gid != $defaultGroup && $gid != $primaryAdminGroup) {
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

    public function userMenu(FactoryInterface $factory, array $options)
    {
        $this->setTranslator($this->container->get('translator'));
        $permissionApi = $this->container->get('zikula_permissions_module.api.permission');
        /** @var GroupEntity $group */
        $group = $options['group'];
        $gid = $group->getGid();
        $menu = $factory->createItem('userActions');
        $menu->setChildrenAttribute('class', 'list-inline');
        $requestAttributes = $this->container->get('request')->attributes->all();
        $currentUserId = $this->container->get('zikula_users_module.current_user')->get('uid');
        if (null !== $currentUserId) {
            $currentUser = $this->container->get('zikula_users_module.user_repository')->find($currentUserId);
        }

        if ($permissionApi->hasPermission('ZikulaGroupsModule::', $gid . '::', ACCESS_READ)
            && ('zikulagroupsmodule_membership_list' != $requestAttributes['_route'])
            && ($group->getGtype() == CommonHelper::GTYPE_PUBLIC
                || ($group->getGtype() == CommonHelper::GTYPE_PRIVATE && isset($currentUser) && $group->getUsers()->contains($currentUser)))
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
            } elseif ($group->getGtype() == CommonHelper::GTYPE_PRIVATE) {
                $existingApplication = $this->container->get('zikula_groups_module.group_application_repository')->findOneBy(['group' => $group, 'user' => $currentUser]);
                if ($existingApplication) {
                    $menu->addChild($this->__('Applied!'));
                } else {
                    $menu->addChild($this->__f('Apply to membership of ":name" group', [':name' => $group->getName()]), [
                        'route' => 'zikulagroupsmodule_application_create',
                        'routeParameters' => ['gid' => $gid],
                    ])->setAttribute('icon', 'fa fa-paper-plane');
                }
            } elseif ($group->getState() !== CommonHelper::STATE_CLOSED) {
                $menu->addChild($this->__f('Join ":name" group', [':name' => $group->getName()]), [
                    'route' => 'zikulagroupsmodule_membership_join',
                    'routeParameters' => ['gid' => $gid],
                ])->setAttribute('icon', 'fa fa-user-plus text-success');
            }
        } else {
            $returnUrl = $this->container->get('router')->generate('zikulagroupsmodule_membership_list', ['gid' => $gid], UrlGeneratorInterface::ABSOLUTE_URL);
            $menu->addChild($this->__('Log in or register'), [
                'route' => 'zikulausersmodule_access_login',
                'routeParameters' => ['returnUrl' => $returnUrl]
            ])->setAttribute('icon', 'fa fa-key');
        }

        return $menu;
    }
}
