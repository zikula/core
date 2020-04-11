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

namespace Zikula\ExtensionsModule\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Zikula\Bundle\CoreBundle\Composer\MetaData;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\ExtensionsModule\Constant;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

class MenuBuilder
{
    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    /**
     * @var CsrfTokenManagerInterface
     */
    private $csrfTokenManager;

    public function __construct(
        FactoryInterface $factory,
        PermissionApiInterface $permissionApi,
        CsrfTokenManagerInterface $csrfTokenManager
    ) {
        $this->factory = $factory;
        $this->permissionApi = $permissionApi;
        $this->csrfTokenManager = $csrfTokenManager;
    }

    public function createAdminMenu(array $options): ItemInterface
    {
        /** @var \Zikula\ExtensionsModule\Entity\ExtensionEntity $extension */
        $extension = $options['extension'];
        $menu = $this->factory->createItem('adminActions');
        $menu->setChildrenAttribute('class', 'list-inline');

        if (!$this->permissionApi->hasPermission('ZikulaExtensionsModule::', $extension->getName() . '::' . $extension->getId(), ACCESS_ADMIN)) {
            return $menu;
        }

        $id = $extension->getId();

        switch ($extension->getState()) {
            case Constant::STATE_ACTIVE:
                if (!ZikulaKernel::isCoreExtension($extension->getName())) {
                    $csrfToken = $this->getCsrfToken('deactivate-extension');
                    $menu->addChild('Deactivate extension', [
                        'route' => 'zikulaextensionsmodule_extension_deactivate',
                        'routeParameters' => [
                            'id' => $id,
                            'token' => $csrfToken
                        ]
                    ])->setAttribute('icon', 'fas fa-minus-circle text-danger');
                }
                break;
            case Constant::STATE_INACTIVE:
                $csrfToken = $this->getCsrfToken('activate-extension');
                $menu->addChild('Activate extension', [
                    'route' => 'zikulaextensionsmodule_extension_activate',
                    'routeParameters' => ['id' => $id, 'token' => $csrfToken]
                ])->setAttribute('icon', 'fas fa-plus-square')
                    ->setLinkAttribute('class', 'text-success');
                $csrfToken = $this->getCsrfToken('uninstall-extension');
                $menu->addChild('Uninstall extension', [
                    'route' => 'zikulaextensionsmodule_extension_uninstall',
                    'routeParameters' => ['id' => $id, 'token' => $csrfToken]
                ])->setAttribute('icon', 'fas fa-trash-alt text-danger');
                break;
            case Constant::STATE_MISSING:
                // Nothing to do.
                // do not allow deletion of missing modules if previously installed (#1278)
                break;
            case Constant::STATE_UPGRADED:
                $csrfToken = $this->getCsrfToken('upgrade-extension');
                $menu->addChild('Upgrade extension', [
                    'route' => 'zikulaextensionsmodule_extension_upgrade',
                    'routeParameters' => ['id' => $id, 'token' => $csrfToken]
                ])->setAttribute('icon', 'fas fa-sync text-primary');
                break;
            case Constant::STATE_INVALID:
                // nothing to do.
                // do not allow deletion of invalid modules if previously installed (#1278)
                break;
            case Constant::STATE_NOTALLOWED:
                $csrfToken = $this->getCsrfToken('uninstall-extension');
                $menu->addChild('Remove extension', [
                    'route' => 'zikulaextensionsmodule_extension_uninstall',
                    'routeParameters' => ['id' => $id, 'token' => $csrfToken]
                ])->setAttribute('icon', 'fas fa-trash-alt text-danger');
                break;
            case Constant::STATE_UNINITIALISED:
            default:
                if ($extension->getState() < 10) {
                    $csrfToken = $this->getCsrfToken('install-extension');
                    $menu->addChild('Install extension', [
                        'route' => 'zikulaextensionsmodule_extension_install',
                        'routeParameters' => [
                            'id' => $id,
                            'token' => $csrfToken
                        ]
                    ])->setAttribute('icon', 'fas fa-cog')
                        ->setLinkAttribute('class', 'text-success');
                } else {
                    $menu->addChild('Core compatibility information', [
                        'route' => 'zikulaextensionsmodule_extension_compatibility',
                        'routeParameters' => ['id' => $id]
                    ])->setAttribute('icon', 'fas fa-info-circle text-info');
                }
                break;
        }

        if (in_array($extension->getState(), [
            Constant::STATE_ACTIVE,
            Constant::STATE_INACTIVE,
        ], true)) {
            $menu->addChild('Edit extension', [
                'route' => 'zikulaextensionsmodule_extension_modify',
                'routeParameters' => ['id' => $id]
            ])->setAttribute('icon', 'fas fa-wrench text-dark');
        }

        if (Constant::STATE_ACTIVE === $extension->getState()
            && (in_array($extension->getType(), [MetaData::TYPE_THEME, MetaData::TYPE_SYSTEM_THEME]))) {
            $menu->addChild('Edit theme vars', [
                'route' => 'zikulathememodule_var_var',
                'routeParameters' => ['themeName' => $extension->getName()]
            ])->setAttribute('icon', 'fas fa-pencil-alt');
            $menu->addChild('Preview theme', [
                'route' => 'zikulaextensionsmodule_extension_preview',
                'routeParameters' => ['themeName' => $extension->getName()]
            ])->setAttribute('icon', 'far fa-eye')
            ->setLinkAttribute('target', '_blank');
        }

        return $menu;
    }

    private function getCsrfToken(string $tokenId): string
    {
        return $this->csrfTokenManager->getToken($tokenId)->getValue();
    }
}
