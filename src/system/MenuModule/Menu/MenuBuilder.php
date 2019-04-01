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

namespace Zikula\MenuModule\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
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

        $title = $this->__('Home');
        $menu->addChild($title, ['route' => 'home'])
            ->setAttribute('icon', 'fa fa-list')
            ->setAttribute('dropdown', true);

        $adminModules = $this->capabilityApi->getExtensionsCapableOf('admin');
        /** @var ExtensionEntity[] $adminModules */
        foreach ($adminModules as $adminModule) {
            if (isset($adminModule->getCapabilities()['admin']['route'])) {
                $menu[$title]->addChild($adminModule->getDisplayname(), [
                    'route' => $adminModule->getCapabilities()['admin']['route'],
                    'routeParameters' => []
                ])->setAttribute('icon', 'fa fa-star');
            }
        }

        return $menu;
    }

    public function createAdminActionsMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('menuModuleAdminActionsMenu');
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

    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }
}
