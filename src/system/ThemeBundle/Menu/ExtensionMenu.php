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
use Symfony\Component\HttpFoundation\RequestStack;
use Translation\Bundle\EditInPlace\Activator as EditInPlaceActivator;
use Zikula\Bundle\CoreBundle\Api\ApiInterface\LocaleApiInterface;
use Zikula\PermissionsBundle\Api\ApiInterface\PermissionApiInterface;
use Zikula\ThemeBundle\ExtensionMenu\AbstractExtensionMenu;

class ExtensionMenu extends AbstractExtensionMenu
{
    public function __construct(
        protected readonly PermissionApiInterface $permissionApi,
        private readonly RequestStack $requestStack,
        private readonly LocaleApiInterface $localeApi,
        private readonly string $environment
    ) {
    }

    protected function getAdmin(): iterable
    {
        if (!$this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_READ)) {
            return;
        }

        yield MenuItem::linktoRoute('Bundle categories list', 'fas fa-list', 'zikulathemebundle_admin_view');

        if ($this->localeApi->multilingual() && 'dev' === $this->environment) {
            $localizationItems = [];
            $request = $this->requestStack->getCurrentRequest();
            if ($request->hasSession() && ($session = $request->getSession())) {
                if ($session->has(EditInPlaceActivator::KEY)) {
                    $localizationItems[] = MenuItem::linktoRoute('Disable edit in place', 'fas fa-ban', 'zikulathemebundle_localization_toggleeditinplace');
                } else {
                    $localizationItems[] = MenuItem::linktoRoute('Enable edit in place', 'fas fa-user-edit', 'zikulathemebundle_localization_toggleeditinplace');
                }
            }
            $localizationItems[] = MenuItem::linktoRoute('Translation UI', 'fas fa-language', 'translation_index');
            yield MenuItem::subMenu('Localization', 'fa fa-globe')->setSubItems($localizationItems);
        }

        yield MenuItem::linktoRoute('Branding', 'fas fa-palette', 'zikulathemebundle_branding_overview');
        yield MenuItem::linktoRoute('Test mail settings', 'fas fa-envelope', 'zikulathemebundle_tool_testmail');
        yield MenuItem::linktoRoute('PHP configuration', 'fab fa-php', 'zikulathemebundle_tool_phpinfo');
    }

    public function getBundleName(): string
    {
        return 'ZikulaThemeBundle';
    }
}
