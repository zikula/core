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

namespace Zikula\ThemeBundle\Menu;

use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Translation\Bundle\EditInPlace\Activator as EditInPlaceActivator;
use Zikula\Bundle\CoreBundle\Api\ApiInterface\LocaleApiInterface;
use Zikula\ThemeBundle\ExtensionMenu\AbstractExtensionMenu;

class ExtensionMenu extends AbstractExtensionMenu
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly LocaleApiInterface $localeApi,
        #[Autowire('%kernel.environment%')]
        private readonly string $environment
    ) {
    }

    protected function getAdmin(): iterable
    {
        yield MenuItem::linktoRoute('Bundle categories list', 'fas fa-list', 'zikulathemebundle_admin_view')
            ->setPermission('ROLE_ADMIN');

        if ($this->localeApi->multilingual() && 'dev' === $this->environment) {
            $localizationItems = [];
            $request = $this->requestStack->getCurrentRequest();
            if ($request->hasSession() && ($session = $request->getSession())) {
                if ($session->has(EditInPlaceActivator::KEY)) {
                    $localizationItems[] = MenuItem::linktoRoute('Disable edit in place', 'fas fa-ban', 'zikulathemebundle_localization_toggleeditinplace')
                        ->setPermission('ROLE_ADMIN');
                } else {
                    $localizationItems[] = MenuItem::linktoRoute('Enable edit in place', 'fas fa-user-edit', 'zikulathemebundle_localization_toggleeditinplace')
                        ->setPermission('ROLE_ADMIN');
                }
            }
            $localizationItems[] = MenuItem::linktoRoute('Translation UI', 'fas fa-language', 'translation_index')
                ->setPermission('ROLE_ADMIN');
            yield MenuItem::subMenu('Localization', 'fa fa-globe')->setSubItems($localizationItems)
                ->setPermission('ROLE_ADMIN');
        }

        yield MenuItem::linktoRoute('Branding', 'fas fa-palette', 'zikulathemebundle_branding_overview')
            ->setPermission('ROLE_ADMIN');
        yield MenuItem::linktoRoute('Test mail settings', 'fas fa-envelope', 'zikulathemebundle_tool_testmail')
            ->setPermission('ROLE_ADMIN');
        yield MenuItem::linktoRoute('PHP configuration', 'fab fa-php', 'zikulathemebundle_tool_phpinfo')
            ->setPermission('ROLE_ADMIN');
    }

    public function getBundleName(): string
    {
        return 'ZikulaThemeBundle';
    }
}
