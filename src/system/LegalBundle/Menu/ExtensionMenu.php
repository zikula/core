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

use EasyCorp\Bundle\EasyAdminBundle\Config\Menu\RouteMenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Menu\UrlMenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use Zikula\ThemeBundle\ExtensionMenu\AbstractExtensionMenu;

class ExtensionMenu extends AbstractExtensionMenu
{
    public function __construct(private readonly array $legalConfig)
    {
    }

    protected function getAdmin(): iterable
    {
        yield MenuItem::linktoRoute('Settings', 'fas fa-wrench', 'zikulalegalbundle_config_config')
            ->setPermission('ROLE_ADMIN');
    }

    protected function getUser(): iterable
    {
        $policies = $this->legalConfig['policies'];

        if ($policies['legal_notice']['enabled']) {
            yield $this->getMenuItem('Legal notice', 'legalnotice', $policies['legal_notice']['custom_url']);
        }
        if ($policies['privacy_policy']['enabled']) {
            yield $this->getMenuItem('Privacy policy', 'privacypolicy', $policies['privacy_policy']['custom_url']);
        }
        if ($policies['terms_of_use']['enabled']) {
            yield $this->getMenuItem('Terms of use', 'termsofuse', $policies['terms_of_use']['custom_url']);
        }
        if ($policies['trade_conditions']['enabled']) {
            yield $this->getMenuItem('Trade conditions', 'tradeconditions', $policies['trade_conditions']['custom_url']);
        }
        if ($policies['cancellation_right_policy']['enabled']) {
            yield $this->getMenuItem('Cancellation right policy', 'cancellationrightpolicy', $policies['cancellation_right_policy']['custom_url']);
        }
        if ($policies['accessibility']['enabled']) {
            yield $this->getMenuItem('Accessibility statement', 'accessibilitystatement', $policies['accessibility']['custom_url']);
        }
    }

    protected function getAccount(): iterable
    {
        yield MenuItem::linktoRoute('Legal Docs', 'fas fa-gavel', 'zikulalegalbundle_user_index');
    }

    private function getMenuItem(string $title, string $defaultRoute, ?string $customUrl): RouteMenuItem|UrlMenuItem
    {
        if (null !== $customUrl && '' !== $customUrl) {
            return MenuItem::linktoUrl($title, null, $customUrl);
        }

        return MenuItem::linktoRoute($title, null, 'zikulalegalbundle_user_' . $defaultRoute);
    }

    public function getBundleName(): string
    {
        return 'ZikulaLegalBundle';
    }
}
