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
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\ExtensionsModule\Constant;
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

    /**
     * @var CsrfTokenManagerInterface
     */
    private $csrfTokenManager;

    public function __construct(
        TranslatorInterface $translator,
        FactoryInterface $factory,
        PermissionApiInterface $permissionApi,
        CsrfTokenManagerInterface $csrfTokenManager
    ) {
        $this->setTranslator($translator);
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
                    $menu->addChild($this->__f('Deactivate %s', ['%s' => $extension->getDisplayname()]), [
                        'route' => 'zikulaextensionsmodule_module_deactivate',
                        'routeParameters' => ['id' => $id, 'token' => $csrfToken]
                    ])->setAttribute('icon', 'fa fa-minus-circle')
                    ->setLinkAttribute('class', 'text-danger');
                    // or set style text-color #0c00
                }
                break;
            case Constant::STATE_INACTIVE:
                $csrfToken = $this->getCsrfToken('activate-extension');
                $menu->addChild($this->__f('Activate %s', ['%s' => $extension->getDisplayname()]), [
                    'route' => 'zikulaextensionsmodule_module_activate',
                    'routeParameters' => ['id' => $id, 'token' => $csrfToken]
                ])->setAttribute('icon', 'fa fa-plus-square')
                    ->setLinkAttribute('class', 'text-success');
                $csrfToken = $this->getCsrfToken('uninstall-extension');
                $menu->addChild($this->__f('Uninstall %s', ['%s' => $extension->getDisplayname()]), [
                    'route' => 'zikulaextensionsmodule_module_uninstall',
                    'routeParameters' => ['id' => $id, 'token' => $csrfToken]
                ])->setAttribute('icon', 'fa fa-trash-o')
                    ->setLinkAttribute('style', 'color:#c00');
                break;
            case Constant::STATE_MISSING:
                // Nothing to do.
                break;
            case Constant::STATE_UPGRADED:
                $csrfToken = $this->getCsrfToken('upgrade-extension');
                $menu->addChild($this->__f('Upgrade %s', ['%s' => $extension->getDisplayname()]), [
                    'route' => 'zikulaextensionsmodule_module_upgrade',
                    'routeParameters' => ['id' => $id, 'token' => $csrfToken]
                ])->setAttribute('icon', 'fa fa-refresh')
                    ->setLinkAttribute('style', 'color:#00c');
                break;
            case Constant::STATE_INVALID:
                // nothing to do.
                // do not allow deletion of invalid modules if previously installed (#1278)
                break;
            case Constant::STATE_NOTALLOWED:
                $csrfToken = $this->getCsrfToken('uninstall-extension');
                $menu->addChild($this->__f('Remove %s', ['%s' => $extension->getDisplayname()]), [
                    'route' => 'zikulaextensionsmodule_module_uninstall',
                    'routeParameters' => ['id' => $id, 'token' => $csrfToken]
                ])->setAttribute('icon', 'fa fa-trash-o')
                    ->setLinkAttribute('style', 'color:#c00');
                break;
            case Constant::STATE_UNINITIALISED:
            default:
                if ($extension->getState() < 10) {
                    $csrfToken = $this->getCsrfToken('install-extension');
                    $menu->addChild($this->__f('Install %s', ['%s' => $extension->getDisplayname()]), [
                        'route' => 'zikulaextensionsmodule_module_install',
                        'routeParameters' => ['id' => $id, 'token' => $csrfToken]
                    ])->setAttribute('icon', 'fa fa-cog')
                        ->setLinkAttribute('class', 'text-success');
                } else {
                    $menu->addChild($this->__f('Core compatibility information: %s', ['%s' => $extension->getDisplayname()]), [
                        'route' => 'zikulaextensionsmodule_module_compatibility',
                        'routeParameters' => ['id' => $id]
                    ])->setAttribute('icon', 'fa fa-info-circle')
                        ->setLinkAttribute('style', 'color:black');
                }
                break;
        }

        if (!in_array($extension->getState(), [Constant::STATE_UNINITIALISED, Constant::STATE_INVALID], true)) {
            $menu->addChild($this->__f('Edit %s', ['%s' => $extension->getDisplayname()]), [
                'route' => 'zikulaextensionsmodule_module_modify',
                'routeParameters' => ['id' => $id]
            ])->setAttribute('icon', 'fa fa-wrench')
                ->setLinkAttribute('style', 'color:black');
        }

        return $menu;
    }

    private function getCsrfToken(string $tokenId): string
    {
        return $this->csrfTokenManager->getToken($tokenId)->getValue();
    }

    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }
}
