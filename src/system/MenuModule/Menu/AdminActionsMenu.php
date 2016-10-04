<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\MenuModule\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class AdminActionsMenu implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function menu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('adminActions');
        $menu->setChildrenAttribute('class', 'list-inline');
        $menu->addChild('Edit Children', [
                'route' => 'zikulamenumodule_menu_view',
                'routeParameters' => $options,
            ])->setAttribute('icon', 'fa fa-child');
        $menu->addChild('Edit Menu Root', [
                'route' => 'zikulamenumodule_menu_edit',
                'routeParameters' => $options,
            ])->setAttribute('icon', 'fa fa-tree');
        $menu->addChild('Delete', [
                'route' => 'zikulamenumodule_menu_delete',
                'routeParameters' => $options,
            ])->setAttribute('icon', 'fa fa-trash-o');

        return $menu;
    }
}
