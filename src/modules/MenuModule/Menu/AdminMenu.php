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
use Zikula\ExtensionsModule\Entity\ExtensionEntity;

class AdminMenu implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function adminMenu(FactoryInterface $factory, array $options)
    {
        // @see https://gist.github.com/nateevans/9958390
        $menu = $factory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav navbar-nav');

        $menu->addChild('Home', ['route' => 'home'])
            ->setAttribute('icon', 'fa fa-list')
            ->setAttribute('dropdown', true);

        $adminModules = $this->container->get('zikula_extensions_module.api.capability')->getExtensionsCapableOf('admin');
        /** @var ExtensionEntity[] $adminModules */
        foreach ($adminModules as $adminModule) {
            if (isset($adminModule->getCapabilities()['admin']['route'])) {
                $menu['Home']
                    ->addChild($adminModule->getDisplayname(), [
                    'route' => $adminModule->getCapabilities()['admin']['route'],
                    'routeParameters' => []
                    ])
                    ->setAttribute('icon', 'fa fa-star');
            }
        }

        return $menu;
    }
}
