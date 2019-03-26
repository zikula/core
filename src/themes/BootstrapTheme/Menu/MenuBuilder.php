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

namespace Zikula\BootstrapTheme\Menu;

use Knp\Menu\FactoryInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

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

    public function __construct(TranslatorInterface $translator, FactoryInterface $factory, PermissionApiInterface $permissionApi)
    {
        $this->setTranslator($translator);
        $this->factory = $factory;
        $this->permissionApi = $permissionApi;
    }

    public function createAdminMenu(array $options)
    {
        $menu = $this->factory->createItem('bootstrapThemeAdminMenu');
        $menu->setChildrenAttribute('class', 'nav navbar-nav');
        $menu->addChild($this->__('Home'), ['route' => 'home']);
        if ($this->permissionApi->hasPermission('ZikulaSettingsModule::', '::', ACCESS_ADMIN)) {
            $menu->addChild($this->__('Settings'), ['route' => 'zikulasettingsmodule_settings_main']);
        }
        if ($this->permissionApi->hasPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
            $menu->addChild($this->__('Extensions'), ['route' => 'zikulaextensionsmodule_module_viewmodulelist']);
        }
        if ($this->permissionApi->hasPermission('ZikulaBlocksModule::', '::', ACCESS_EDIT)) {
            $menu->addChild($this->__('Blocks'), ['route' => 'zikulablocksmodule_admin_view']);
        }
        if ($this->permissionApi->hasPermission('ZikulaUsersModule::', '::', ACCESS_MODERATE)) {
            $menu->addChild($this->__('Users'), ['route' => 'zikulausersmodule_useradministration_list']);
        }
        if ($this->permissionApi->hasPermission('ZikulaGroupsModule::', '::', ACCESS_EDIT)) {
            $menu->addChild($this->__('Groups'), ['route' => 'zikulagroupsmodule_group_adminlist']);
        }
        if ($this->permissionApi->hasPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            $menu->addChild($this->__('Permissions'), ['route' => 'zikulapermissionsmodule_permission_list']);
        }
        if ($this->permissionApi->hasPermission('ZikulaThemeModule::', '::', ACCESS_EDIT)) {
            $menu->addChild($this->__('Themes'), ['route' => 'zikulathememodule_theme_view']);
        }

        return $menu;
    }

    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }
}
