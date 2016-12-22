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
        $this->setTranslator($this->container->get('translator'));
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
            'route' => 'zikulagroupsmodule_membershipadministration_list',
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

        if ($permissionApi->hasPermission('ZikulaGroupsModule::', $gid . '::', ACCESS_READ)) {
            $menu->addChild($this->__f('View membership of ":name" group', [':name' => $group->getName()]), [
                'route' => 'zikulagroupsmodule_user_memberslist',
                'routeParameters' => ['gid' => $gid],
            ])->setAttribute('icon', 'fa fa-users');
        }
        $currentUserId = $this->container->get('zikula_users_module.current_user')->get('uid');
        if (null !== $currentUserId) {
            $currentUser = $this->container->get('zikula_users_module.user_repository')->find($currentUserId);
            if ($group->getUsers()->contains($currentUser)) {
                $menu->addChild($this->__f('Resign membership of ":name" group', [':name' => $group->getName()]), [
                    'route' => 'zikulagroupsmodule_user_membership',
                    'routeParameters' => ['action' => 'unsubscribe', 'gid' => $gid],
                ])->setAttribute('icon', 'fa fa-user-times text-danger');
            } elseif ($group->getState() !== CommonHelper::STATE_CLOSED) {
                $menu->addChild($this->__f('Apply to membership of ":name" group', [':name' => $group->getName()]), [
                    'route' => 'zikulagroupsmodule_user_membership',
                    'routeParameters' => ['action' => 'subscribe', 'gid' => $gid],
                ])->setAttribute('icon', 'fa fa-user-plus text-success');
            }
        }

        return $menu;
    }
}
