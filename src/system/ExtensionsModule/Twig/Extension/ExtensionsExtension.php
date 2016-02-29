<?php
/**
 * Copyright Zikula Foundation 2016 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv3 (or at your option any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\ExtensionsModule\Twig\Extension;

use Symfony\Component\Routing\RouterInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Core\Token\CsrfTokenHandler;
use Zikula\ExtensionsModule\Api\ExtensionApi;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\PermissionsModule\Api\PermissionApi;

class ExtensionsExtension extends \Twig_Extension
{
    /**
     * @var TranslatorInterface
     */
    private $translator;
    /**
     * @var PermissionApi
     */
    private $permissionApi;
    /**
     * @var CsrfTokenHandler
     */
    private $tokenHandler;
    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var ExtensionApi
     */
    private $extensinApi;

    /**
     * ExtensionsExtension constructor.
     * @param $translator
     */
    public function __construct(
        TranslatorInterface $translator,
        PermissionApi $permissionApi,
        CsrfTokenHandler $tokenHandler,
        RouterInterface $router,
        ExtensionApi $extensionApi
    ) {
        $this->translator = $translator;
        $this->permissionApi = $permissionApi;
        $this->tokenHandler = $tokenHandler;
        $this->router = $router;
        $this->extensinApi = $extensionApi;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'zikulaextensionsmodule';
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('stateLabel', [$this, 'stateLabel'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('extensionActions', [$this, 'extensionActions']),
        ];
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('isCoreModule', [$this, 'isCoreModule']),
        ];
    }

    public function stateLabel(ExtensionEntity $extensionEntity, array $upgradedExtensions = null)
    {
        switch ($extensionEntity->getState()) {
            case \ModUtil::STATE_INACTIVE:
                $status = $this->translator->__('Inactive');
                $statusclass = "warning";
                break;
            case \ModUtil::STATE_ACTIVE:
                $status = $this->translator->__('Active');
                $statusclass = "success";
                break;
            case \ModUtil::STATE_MISSING:
                $status = $this->translator->__('Files missing');
                $statusclass = "danger";
                break;
            case \ModUtil::STATE_UPGRADED:
                $status = $this->translator->__('New version');
                $statusclass = "danger";
                break;
            case \ModUtil::STATE_INVALID:
                $status = $this->translator->__('Invalid structure');
                $statusclass = "danger";
                break;
            case \ModUtil::STATE_NOTALLOWED:
                $status = $this->translator->__('Not allowed');
                $statusclass = "danger";
                break;
            case \ModUtil::STATE_UNINITIALISED:
            default:
                if ($extensionEntity->getState() > 10) {
                    $status = $this->translator->__('Incompatible');
                    $statusclass = "info";
                } else {
                    $status = $this->translator->__('Not installed');
                    $statusclass = "primary";
                }
                break;
        }

        $newVersionString = ($extensionEntity->getState() == ExtensionApi::STATE_UPGRADED) ? '&nbsp;<span class="label label-warning">' . $upgradedExtensions[$extensionEntity->getName()] . '</span>' : null;

        return '<span class="label label-' . $statusclass . '">' . $status . '</span>' . $newVersionString;
    }

    public function isCoreModule($moduleName)
    {
        return $this->extensinApi->isCoreModule($moduleName);
    }

    public function extensionActions(ExtensionEntity $extension)
    {
        if (!$this->permissionApi->hasPermission('ZikulaExtensionsModule::', $extension->getName() . '::' . $extension->getId(), ACCESS_ADMIN)) {
            return [];
        }

        $csrfToken = $this->tokenHandler->generate(true);

        $actions = [];
        switch ($extension->getState()) {
            case ExtensionApi::STATE_ACTIVE:
                if (!$this->isCoreModule($extension->getName())) {
                    $actions[] = [
                        'url' => $this->router->generate('zikulaextensionsmodule_module_deactivate', ['id' => $extension->getId(), 'csrftoken' => $csrfToken]),
                        'icon' => 'minus-circle text-danger',
                        'color' => '#c00',
                        'title' => $this->translator->__f('Deactivate %s', ['%s' => $extension->getDisplayname()])
                    ];
                }
                if (\PluginUtil::hasModulePlugins($extension->getName())) {
                    $actions[] = [
                        'url' => $this->router->generate('zikulaextensionsmodule_admin_viewplugins', [
                            'bymodule' => $extension->getName(),
                            'csrftoken' => $csrfToken]),
                        'icon' => 'plug',
                        'color' => 'black',
                        'title' => $this->translator->__f('Plugins for %s', ['%s' => $extension->getDisplayname()])
                    ];
                }
                break;
            case ExtensionApi::STATE_INACTIVE:
                $actions[] = [
                    'url' => $this->router->generate('zikulaextensionsmodule_module_activate', ['id' => $extension->getId(), 'csrftoken' => $csrfToken]),
                    'icon' => 'plus-square text-success',
                    'color' => '#0c0',
                    'title' => $this->translator->__f('Activate %s', ['%s' => $extension->getDisplayname()])
                ];
                $actions[] = [
                    'url' => $this->router->generate('zikulaextensionsmodule_module_uninstall', ['id' => $extension->getId()]),
                    'icon' => 'trash-o',
                    'color' => '#c00',
                    'title' => $this->translator->__f('Uninstall %s', ['%s' => $extension->getDisplayname()])
                ];
                break;
            case ExtensionApi::STATE_MISSING:
                // Nothing to do.
                break;
            case ExtensionApi::STATE_UPGRADED:
                $actions[] = [
                    'url' => $this->router->generate('zikulaextensionsmodule_module_upgrade', ['id' => $extension->getId(), 'csrftoken' => $csrfToken]),
                    'icon' => 'refresh',
                    'color' => '#00c',
                    'title' => $this->translator->__f('Upgrade %s', ['%s' => $extension->getDisplayname()])
                ];
                break;
            case ExtensionApi::STATE_INVALID:
                // nothing to do.
                // do not allow deletion of invalid modules if previously installed (#1278)
                break;
            case ExtensionApi::STATE_NOTALLOWED:
                $actions[] = [
                    'url' => $this->router->generate('zikulaextensionsmodule_module_uninstall', ['id' => $extension->getId()]),
                    'icon' => 'trash-o',
                    'color' => '#c00',
                    'title' => $this->translator->__f('Remove %s', ['%s' => $extension->getDisplayname()])
                ];
                break;
            case ExtensionApi::STATE_UNINITIALISED:
            default:
                if ($extension->getState() < 10) {
                    $actions[] = [
                        'url' => $this->router->generate('zikulaextensionsmodule_module_install', ['id' => $extension->getId()]),
                        'icon' => 'cog text-success',
                        'color' => '#0c0',
                        'title' => $this->translator->__f('Install %s', ['%s' => $extension->getDisplayname()])
                    ];
                } else {
                    $actions[] = [
                        'url' => $this->router->generate('zikulaextensionsmodule_module_compatibility', ['id' => $extension->getId()]),
                        'icon' => 'info-circle',
                        'color' => 'black',
                        'title' => $this->translator->__f('Core compatibility information: %s', ['%s' => $extension->getDisplayname()])
                    ];
                }
                break;
        }

        if ($extension->getState() != ExtensionApi::STATE_INVALID) {
            $actions[] = [
                'url' => $this->router->generate('zikulaextensionsmodule_module_modify', ['id' => $extension->getId()]),
                'icon' => 'wrench',
                'color' => 'black',
                'title' => $this->translator->__f('Edit %s', ['%s' => $extension->getDisplayname()])
            ];
        }

        return $actions;
    }
}
