<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BootstrapTheme\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Zikula\Common\Translator\TranslatorTrait;

class AdminMenu implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    use TranslatorTrait;

    public function adminMenu(FactoryInterface $factory, array $options)
    {
        $this->setTranslator($this->container->get('translator.default'));
        $permApi = $this->container->get('zikula_permissions_module.api.permission');
        $menu = $factory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav navbar-nav');
        $menu->addChild($this->__('Home'), ['route' => 'home']);
        if ($permApi->hasPermission('ZikulaSettingsModule::', '::', ACCESS_ADMIN)) {
            $menu->addChild($this->__('Settings'), ['route' => 'zikulasettingsmodule_settings_main']);
        }
        if ($permApi->hasPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
            $menu->addChild($this->__('Extensions'), ['route' => 'zikulaextensionsmodule_module_viewmodulelist']);
        }
        if ($permApi->hasPermission('ZikulaBlocksModule::', '::', ACCESS_EDIT)) {
            $menu->addChild($this->__('Blocks'), ['route' => 'zikulablocksmodule_admin_view']);
        }
        if ($permApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_MODERATE)) {
            $menu->addChild($this->__('Users'), ['route' => 'zikulausersmodule_useradministration_list']);
        }
        if ($permApi->hasPermission('ZikulaGroupsModule::', '::', ACCESS_EDIT)) {
            $menu->addChild($this->__('Groups'), ['route' => 'zikulagroupsmodule_admin_index']);
        }
        if ($permApi->hasPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            $menu->addChild($this->__('Permissions'), ['route' => 'zikulapermissionsmodule_admin_index']);
        }
        if ($permApi->hasPermission('ZikulaThemeModule::', '::', ACCESS_EDIT)) {
            $menu->addChild($this->__('Themes'), ['route' => 'zikulathememodule_theme_view']);
        }

        return $menu;
    }

    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }
}
