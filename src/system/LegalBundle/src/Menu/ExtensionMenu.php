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
use Symfony\Component\Translation\TranslatableMessage;
use Zikula\ThemeBundle\ExtensionMenu\AbstractExtensionMenu;
use function Symfony\Component\Translation\t;

class ExtensionMenu extends AbstractExtensionMenu
{
    public function __construct(private readonly array $legalConfig)
    {
    }

    protected function getUser(): iterable
    {
        $policies = $this->legalConfig['policies'];

        if ($policies['legal_notice']['enabled']) {
            yield $this->getMenuItem(t('Legal notice'), 'legalnotice', $policies['legal_notice']['custom_url']);
        }
        if ($policies['privacy_policy']['enabled']) {
            yield $this->getMenuItem(t('Privacy policy'), 'privacypolicy', $policies['privacy_policy']['custom_url']);
        }
        if ($policies['terms_of_use']['enabled']) {
            yield $this->getMenuItem(t('Terms of use'), 'termsofuse', $policies['terms_of_use']['custom_url']);
        }
        if ($policies['trade_conditions']['enabled']) {
            yield $this->getMenuItem(t('Trade conditions'), 'tradeconditions', $policies['trade_conditions']['custom_url']);
        }
        if ($policies['cancellation_right_policy']['enabled']) {
            yield $this->getMenuItem(t('Cancellation right policy'), 'cancellationrightpolicy', $policies['cancellation_right_policy']['custom_url']);
        }
        if ($policies['accessibility']['enabled']) {
            yield $this->getMenuItem(t('Accessibility statement'), 'accessibilitystatement', $policies['accessibility']['custom_url']);
        }
    }

    protected function getAccount(): iterable
    {
        yield MenuItem::linktoRoute(t('Legal Docs'), 'fas fa-gavel', 'zikula_legal_user_index');
    }

    private function getMenuItem(TranslatableMessage $title, string $defaultRoute, ?string $customUrl): RouteMenuItem|UrlMenuItem
    {
        if (null !== $customUrl && '' !== $customUrl) {
            return MenuItem::linktoUrl($title, null, $customUrl);
        }

        return MenuItem::linktoRoute($title, null, 'zikula_legal_user_' . $defaultRoute);
    }

    public function getBundleName(): string
    {
        return 'ZikulaLegalBundle';
    }
}
