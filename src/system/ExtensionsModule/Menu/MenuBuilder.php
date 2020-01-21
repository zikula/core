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

namespace Zikula\ExtensionsModule\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
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
        $extension = $options['extension'];
        $menu = $this->factory->createItem('adminActions');
        $menu->setChildrenAttribute('class', 'list-inline');

        if (!$this->permissionApi->hasPermission('ZikulaExtensionsModule::', $extension->getName() . '::' . $extension->getId(), ACCESS_ADMIN)) {
            return $menu;
        }

        $id = $extension->getId();

        switch ($extension->getState()) {
            case Constant::STATE_ACTIVE:
                if (!ZikulaKernel::isCoreModule($extension->getName())) {
                    $csrfToken = $this->getCsrfToken('deactivate-extension');
                    $menu->addChild('Deactivate extension', [
                        'route' => 'zikulaextensionsmodule_module_deactivate',
                        'routeParameters' => [
                            'id' => $id,
                            'token' => $csrfToken
                        ]
                    ])->setAttribute('icon', 'fas fa-minus-circle')
                        ->setLinkAttribute('class', 'text-danger');
                    // or set style text-color #0c00
                }
                break;
            case Constant::STATE_INACTIVE:
                $csrfToken = $this->getCsrfToken('activate-extension');
                $menu->addChild('Activate extension', [
                    'route' => 'zikulaextensionsmodule_module_activate',
                    'routeParameters' => ['id' => $id, 'token' => $csrfToken]
                ])->setAttribute('icon', 'fas fa-plus-square')
                    ->setLinkAttribute('class', 'text-success');
                $csrfToken = $this->getCsrfToken('uninstall-extension');
                $menu->addChild('Uninstall extension', [
                    'route' => 'zikulaextensionsmodule_module_uninstall',
                    'routeParameters' => ['id' => $id, 'token' => $csrfToken]
                ])->setAttribute('icon', 'fas fa-trash-alt')
                    ->setLinkAttribute('style', 'color: #c00');
                break;
            case Constant::STATE_MISSING:
                // Nothing to do.
                break;
            case Constant::STATE_UPGRADED:
                $csrfToken = $this->getCsrfToken('upgrade-extension');
                $menu->addChild('Upgrade extension', [
                    'route' => 'zikulaextensionsmodule_module_upgrade',
                    'routeParameters' => ['id' => $id, 'token' => $csrfToken]
                ])->setAttribute('icon', 'fas fa-sync')
                    ->setLinkAttribute('style', 'color: #00c');
                break;
            case Constant::STATE_INVALID:
                // nothing to do.
                // do not allow deletion of invalid modules if previously installed (#1278)
                break;
            case Constant::STATE_NOTALLOWED:
                $csrfToken = $this->getCsrfToken('uninstall-extension');
                $menu->addChild('Remove extension', [
                    'route' => 'zikulaextensionsmodule_module_uninstall',
                    'routeParameters' => ['id' => $id, 'token' => $csrfToken]
                ])->setAttribute('icon', 'fas fa-trash-alt')
                    ->setLinkAttribute('style', 'color: #c00');
                break;
            case Constant::STATE_UNINITIALISED:
            default:
                if ($extension->getState() < 10) {
                    $csrfToken = $this->getCsrfToken('install-extension');
                    $menu->addChild('Install extension', [
                        'route' => 'zikulaextensionsmodule_module_install',
                        'routeParameters' => [
                            'id' => $id,
                            'token' => $csrfToken
                        ]
                    ])->setAttribute('icon', 'fas fa-cog')
                        ->setLinkAttribute('class', 'text-success');
                } else {
                    $menu->addChild('Core compatibility information', [
                        'route' => 'zikulaextensionsmodule_module_compatibility',
                        'routeParameters' => ['id' => $id]
                    ])->setAttribute('icon', 'fas fa-info-circle')
                        ->setLinkAttribute('style', 'color: #000');
                }
                break;
        }

        if (!in_array($extension->getState(), [
            Constant::STATE_UNINITIALISED,
            Constant::STATE_INVALID,
            Constant::STATE_MISSING,
        ], true)) {
            $menu->addChild('Edit extension', [
                'route' => 'zikulaextensionsmodule_module_modify',
                'routeParameters' => ['id' => $id]
            ])->setAttribute('icon', 'fas fa-wrench')
                ->setLinkAttribute('style', 'color: #000');
        }

        return $menu;
    }

    private function getCsrfToken(string $tokenId): string
    {
        return $this->csrfTokenManager->getToken($tokenId)->getValue();
    }
}
