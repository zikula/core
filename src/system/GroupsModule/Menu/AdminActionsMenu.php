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

class AdminActionsMenu implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    use TranslatorTrait;

    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    public function menu(FactoryInterface $factory, array $options)
    {
        $this->setTranslator($this->container->get('translator'));
        $permissionApi = $this->container->get('zikula_permissions_module.api.permission');
        $defaultGroup = $this->container->get('zikula_extensions_module.api.variable')->get('ZikulaGroupsModule', 'defaultgroup');
        $primaryAdminGroup = $this->container->get('zikula_extensions_module.api.variable')->get('ZikulaGroupsModule', 'primaryadmingroup', 2);
        $gid = $options['group']->getGid();
        $routeParams = ['gid' => $gid];
        $menu = $factory->createItem('adminActions');
        $menu->setChildrenAttribute('class', 'list-inline');
        $menu->addChild($this->__f('Edit ":name" group', [':name' => $options['group']->getName()]), [
            'route' => 'zikulagroupsmodule_group_edit',
            'routeParameters' => $routeParams,
        ])->setAttribute('icon', 'fa fa-pencil');
        if ($permissionApi->hasPermission('ZikulaGroupsModule::', $gid . '::', ACCESS_DELETE)
            && $gid != $defaultGroup && $gid != $primaryAdminGroup) {
            $menu->addChild($this->__f('Delete :name', [':name' => $options['group']->getName()]), [
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
}
