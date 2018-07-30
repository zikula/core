<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\MenuModule\Menu;

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
        $menu = $factory->createItem('adminActions');
        $menu->setChildrenAttribute('class', 'list-inline');
        $menu->addChild($this->__('Edit Children'), [
                'route' => 'zikulamenumodule_menu_view',
                'routeParameters' => $options,
            ])->setAttribute('icon', 'fa fa-child');
        $menu->addChild($this->__('Edit Menu Root'), [
                'route' => 'zikulamenumodule_menu_edit',
                'routeParameters' => $options,
            ])->setAttribute('icon', 'fa fa-tree');
        $menu->addChild($this->__('Delete'), [
                'route' => 'zikulamenumodule_menu_delete',
                'routeParameters' => $options,
            ])->setAttribute('icon', 'fa fa-trash-o');

        return $menu;
    }
}
