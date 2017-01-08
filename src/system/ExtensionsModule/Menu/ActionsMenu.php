<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ExtensionsModule\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\ExtensionsModule\Api\ExtensionApi;

class ActionsMenu implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    use TranslatorTrait;

    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    public function adminExtensionsMenu(FactoryInterface $factory, array $options)
    {
        $this->setTranslator($this->container->get('translator'));
        $permissionApi = $this->container->get('zikula_permissions_module.api.permission');
        $extension = $options['extension'];
        $menu = $factory->createItem('adminActions');
        $menu->setChildrenAttribute('class', 'list-inline');

        if (!$permissionApi->hasPermission('ZikulaExtensionsModule::', $extension->getName() . '::' . $extension->getId(), ACCESS_ADMIN)) {
            return $menu;
        }

        $csrfToken = $this->container->get('zikula_core.common.csrf_token_handler')->generate(true);

        switch ($extension->getState()) {
            case ExtensionApi::STATE_ACTIVE:
                if ($extension->getName() == 'ZikulaPageLockModule' || !\ZikulaKernel::isCoreModule($extension->getName())) {
                    $menu->addChild($this->__f('Deactivate %s', ['%s' => $extension->getDisplayname()]), [
                        'route' => 'zikulaextensionsmodule_module_deactivate',
                        'routeParameters' => ['id' => $extension->getId(), 'csrftoken' => $csrfToken],
                    ])->setAttribute('icon', 'fa fa-minus-circle')
                    ->setLinkAttribute('class', 'text-danger');
                    // or set style text-color #0c00
                }
                if (\PluginUtil::hasModulePlugins($extension->getName())) {
                    $menu->addChild($this->__f('Plugins for %s', ['%s' => $extension->getDisplayname()]), [
                        'route' => 'zikulaextensionsmodule_admin_viewplugins',
                        'routeParameters' => ['bymodule' => $extension->getName(),
                            'csrftoken' => $csrfToken],
                    ])->setAttribute('icon', 'fa fa-plug')
                        ->setLinkAttribute('style', 'color:black');
                }
                break;
            case ExtensionApi::STATE_INACTIVE:
                $menu->addChild($this->__f('Activate %s', ['%s' => $extension->getDisplayname()]), [
                    'route' => 'zikulaextensionsmodule_module_activate',
                    'routeParameters' => ['id' => $extension->getId(), 'csrftoken' => $csrfToken],
                ])->setAttribute('icon', 'fa fa-plus-square')
                    ->setLinkAttribute('class', 'text-success');
                $menu->addChild($this->__f('Uninstall %s', ['%s' => $extension->getDisplayname()]), [
                    'route' => 'zikulaextensionsmodule_module_uninstall',
                    'routeParameters' => ['id' => $extension->getId()],
                ])->setAttribute('icon', 'fa fa-trash-o')
                    ->setLinkAttribute('style', 'color:#c00');
                break;
            case ExtensionApi::STATE_MISSING:
                // Nothing to do.
                break;
            case ExtensionApi::STATE_UPGRADED:
                $menu->addChild($this->__f('Upgrade %s', ['%s' => $extension->getDisplayname()]), [
                    'route' => 'zikulaextensionsmodule_module_upgrade',
                    'routeParameters' => ['id' => $extension->getId(), 'csrftoken' => $csrfToken],
                ])->setAttribute('icon', 'fa fa-refresh')
                    ->setLinkAttribute('style', 'color:#00c');
                break;
            case ExtensionApi::STATE_INVALID:
                // nothing to do.
                // do not allow deletion of invalid modules if previously installed (#1278)
                break;
            case ExtensionApi::STATE_NOTALLOWED:
                $menu->addChild($this->__f('Remove %s', ['%s' => $extension->getDisplayname()]), [
                    'route' => 'zikulaextensionsmodule_module_uninstall',
                    'routeParameters' => ['id' => $extension->getId()],
                ])->setAttribute('icon', 'fa fa-trash-o')
                    ->setLinkAttribute('style', 'color:#c00');
                break;
            case ExtensionApi::STATE_UNINITIALISED:
            default:
                if ($extension->getState() < 10) {
                    $menu->addChild($this->__f('Install %s', ['%s' => $extension->getDisplayname()]), [
                        'route' => 'zikulaextensionsmodule_module_install',
                        'routeParameters' => ['id' => $extension->getId()],
                    ])->setAttribute('icon', 'fa fa-cog')
                        ->setLinkAttribute('class', 'text-success');
                } else {
                    $menu->addChild($this->__f('Core compatibility information: %s', ['%s' => $extension->getDisplayname()]), [
                        'route' => 'zikulaextensionsmodule_module_compatibility',
                        'routeParameters' => ['id' => $extension->getId()],
                    ])->setAttribute('icon', 'fa fa-info-circle')
                        ->setLinkAttribute('style', 'color:black');
                }
                break;
        }

        if ($extension->getState() != ExtensionApi::STATE_INVALID) {
            $menu->addChild($this->__f('Edit %s', ['%s' => $extension->getDisplayname()]), [
                'route' => 'zikulaextensionsmodule_module_modify',
                'routeParameters' => ['id' => $extension->getId()],
            ])->setAttribute('icon', 'fa fa-wrench')
                ->setLinkAttribute('style', 'color:black');
        }

        return $menu;
    }
}
