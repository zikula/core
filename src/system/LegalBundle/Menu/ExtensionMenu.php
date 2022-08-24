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

namespace Zikula\LegalBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Zikula\MenuBundle\ExtensionMenu\ExtensionMenuInterface;
use Zikula\PermissionsBundle\Api\ApiInterface\PermissionApiInterface;

class ExtensionMenu implements ExtensionMenuInterface
{
    public function __construct(
        private readonly FactoryInterface $factory,
        private readonly PermissionApiInterface $permissionApi,
        private readonly array $legalConfig
    ) {
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
                'route' => 'zikulalegalbundle_config_config',
            ])->setAttribute('icon', 'fas fa-wrench');
        }

        return 0 === $menu->count() ? null : $menu;
    }

    private function getAccount(): ?ItemInterface
    {
        $menu = $this->factory->createItem('legalAccountMenu');
        $menu->addChild('Legal Docs', [
            'route' => 'zikulalegalbundle_user_index',
        ])->setAttribute('icon', 'fas fa-gavel');

        return 0 === $menu->count() ? null : $menu;
    }

    private function getUser(): ?ItemInterface
    {
        $menu = $this->factory->createItem('legalUserMenu');
        $policies = $this->legalConfig['policies'];

        if ($policies['legal_notice']['enabled']) {
            $menu->addChild('Legal notice', $this->getMenuOptions('legalnotice', $policies['legal_notice']['custom_url']));
        }
        if ($policies['privacy_policy']['enabled']) {
            $menu->addChild('Privacy policy', $this->getMenuOptions('privacypolicy', $policies['privacy_policy']['custom_url']));
        }
        if ($policies['terms_of_use']['enabled']) {
            $menu->addChild('Terms of use', $this->getMenuOptions('termsofuse', $policies['terms_of_use']['custom_url']));
        }
        if ($policies['trade_conditions']['enabled']) {
            $menu->addChild('Trade conditions', $this->getMenuOptions('tradeconditions', $policies['trade_conditions']['custom_url']));
        }
        if ($policies['cancellation_right_policy']['enabled']) {
            $menu->addChild('Cancellation right policy', $this->getMenuOptions('cancellationrightpolicy', $policies['cancellation_right_policy']['custom_url']));
        }
        if ($policies['accessibility']['enabled']) {
            $menu->addChild('Accessibility statement', $this->getMenuOptions('accessibilitystatement', $policies['accessibility']['custom_url']));
        }

        return 0 === $menu->count() ? null : $menu;
    }

    private function getMenuOptions(string $defaultRoute, ?string $customUrl): array
    {
        if (null !== $customUrl && '' !== $customUrl) {
            return ['uri' => $customUrl];
        }

        return ['route' => 'zikulalegalbundle_user_' . $defaultRoute];
    }

    public function getBundleName(): string
    {
        return 'ZikulaLegalBundle';
    }
}
