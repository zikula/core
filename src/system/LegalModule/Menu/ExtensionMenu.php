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

namespace Zikula\LegalModule\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\LegalModule\Constant as LegalConstant;
use Zikula\MenuModule\ExtensionMenu\ExtensionMenuInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

class ExtensionMenu implements ExtensionMenuInterface
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
     * @var VariableApiInterface
     */
    private $variableApi;

    public function __construct(
        FactoryInterface $factory,
        PermissionApiInterface $permissionApi,
        VariableApiInterface $variableApi
    ) {
        $this->factory = $factory;
        $this->permissionApi = $permissionApi;
        $this->variableApi = $variableApi;
    }

    public function get(string $type = self::TYPE_ADMIN): ?ItemInterface
    {
        $method = 'get' . ucfirst(mb_strtolower($type));
        if (method_exists($this, $method)) {
            return $this->{$method}();
        }

        return null;
    }

    private function getAdmin(): ?ItemInterface
    {
        $menu = $this->factory->createItem('legalAdminMenu');
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADMIN)) {
            $menu->addChild('Settings', [
                'route' => 'zikulalegalmodule_config_config',
            ])->setAttribute('icon', 'fas fa-wrench');
        }

        return 0 === $menu->count() ? null : $menu;
    }

    private function getAccount(): ?ItemInterface
    {
        $menu = $this->factory->createItem('legalAccountMenu');
        $menu->addChild('Legal Docs', [
            'route' => 'zikulalegalmodule_user_index',
        ])->setAttribute('icon', 'fas fa-gavel');

        return 0 === $menu->count() ? null : $menu;
    }

    private function getUser(): ?ItemInterface
    {
        $menu = $this->factory->createItem('legalUserMenu');

        if ($this->variableApi->get(LegalConstant::MODNAME, LegalConstant::MODVAR_LEGALNOTICE_ACTIVE)) {
            $menu->addChild('Legal notice', $this->getMenuOptions(LegalConstant::MODVAR_LEGALNOTICE_URL, 'legalnotice'));
        }
        if ($this->variableApi->get(LegalConstant::MODNAME, LegalConstant::MODVAR_TERMS_ACTIVE)) {
            $menu->addChild('Terms of use', $this->getMenuOptions(LegalConstant::MODVAR_TERMS_URL, 'termsofuse'));
        }
        if ($this->variableApi->get(LegalConstant::MODNAME, LegalConstant::MODVAR_PRIVACY_ACTIVE)) {
            $menu->addChild('Privacy policy', $this->getMenuOptions(LegalConstant::MODVAR_PRIVACY_URL, 'privacypolicy'));
        }
        if ($this->variableApi->get(LegalConstant::MODNAME, LegalConstant::MODVAR_TRADECONDITIONS_ACTIVE)) {
            $menu->addChild('Trade conditions', $this->getMenuOptions(LegalConstant::MODVAR_TRADECONDITIONS_URL, 'tradeconditions'));
        }
        if ($this->variableApi->get(LegalConstant::MODNAME, LegalConstant::MODVAR_CANCELLATIONRIGHTPOLICY_ACTIVE)) {
            $menu->addChild('Cancellation right policy', $this->getMenuOptions(LegalConstant::MODVAR_CANCELLATIONRIGHTPOLICY_URL, 'cancellationrightpolicy'));
        }
        if ($this->variableApi->get(LegalConstant::MODNAME, LegalConstant::MODVAR_ACCESSIBILITY_ACTIVE)) {
            $menu->addChild('Accessibility statement', $this->getMenuOptions(LegalConstant::MODVAR_ACCESSIBILITY_URL, 'accessibilitystatement'));
        }

        return 0 === $menu->count() ? null : $menu;
    }

    private function getMenuOptions(string $urlVar, string $defaultRoute): array
    {
        $customUrl = $this->variableApi->get(LegalConstant::MODNAME, $urlVar, '');
        if (null !== $customUrl && '' !== $customUrl) {
            return ['uri' => $customUrl];
        }

        return ['route' => 'zikulalegalmodule_user_' . $defaultRoute];
    }

    public function getBundleName(): string
    {
        return 'ZikulaLegalModule';
    }
}
