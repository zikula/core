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

namespace Zikula\MenuModule\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\Translation\TranslatorTrait;
use Zikula\ExtensionsModule\Api\ApiInterface\CapabilityApiInterface;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;

class MenuBuilder
{
    use TranslatorTrait;

    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var CapabilityApiInterface
     */
    private $capabilityApi;

    public function __construct(
        TranslatorInterface $translator,
        FactoryInterface $factory,
        CapabilityApiInterface $capabilityApi
    ) {
        $this->setTranslator($translator);
        $this->factory = $factory;
        $this->capabilityApi = $capabilityApi;
    }

    public function createAdminMenu(array $options): ItemInterface
    {
        // @see https://gist.github.com/nateevans/9958390
        $menu = $this->factory->createItem('menuModuleAdminMenu');
        $menu->setChildrenAttribute('class', 'nav navbar-nav');

        $title = $this->trans('Home');
        $menu->addChild($title, ['route' => 'home'])
            ->setAttribute('icon', 'fas fa-list')
            ->setAttribute('dropdown', true);

        $adminModules = $this->capabilityApi->getExtensionsCapableOf('admin');
        /** @var ExtensionEntity[] $adminModules */
        foreach ($adminModules as $adminModule) {
            if (isset($adminModule->getCapabilities()['admin']['route'])) {
                $menu[$title]->addChild($adminModule->getDisplayname(), [
                    'route' => $adminModule->getCapabilities()['admin']['route'],
                    'routeParameters' => []
                ])->setAttribute('icon', 'fas fa-star');
            }
        }

        return $menu;
    }

    public function createAdminActionsMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('menuModuleAdminActionsMenu');
        $menu->setChildrenAttribute('class', 'list-inline');
        $menu->addChild($this->trans('Edit children'), [
            'route' => 'zikulamenumodule_menu_view',
            'routeParameters' => $options,
        ])->setAttribute('icon', 'fas fa-child');
        $menu->addChild($this->trans('Edit menu root'), [
            'route' => 'zikulamenumodule_menu_edit',
            'routeParameters' => $options,
        ])->setAttribute('icon', 'fas fa-tree');
        $menu->addChild($this->trans('Delete'), [
            'route' => 'zikulamenumodule_menu_delete',
            'routeParameters' => $options,
        ])->setAttribute('icon', 'fas fa-trash-alt');

        return $menu;
    }
}
