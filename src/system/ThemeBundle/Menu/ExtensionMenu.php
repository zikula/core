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

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Translation\Bundle\EditInPlace\Activator as EditInPlaceActivator;
use Zikula\Bundle\CoreBundle\Api\ApiInterface\LocaleApiInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\PermissionsBundle\Api\ApiInterface\PermissionApiInterface;
use Zikula\ThemeBundle\ExtensionMenu\ExtensionMenuInterface;

class ExtensionMenu implements ExtensionMenuInterface
{
    public function __construct(
        private readonly FactoryInterface $factory,
        private readonly PermissionApiInterface $permissionApi,
        private readonly RequestStack $requestStack,
        private readonly ZikulaHttpKernelInterface $kernel,
        private readonly LocaleApiInterface $localeApi
    ) {
    }

    public function get(string $type = self::TYPE_ADMIN): ?ItemInterface
    {
        if (self::TYPE_ADMIN === $type) {
            return $this->getAdmin();
        }

        return null;
    }

    protected function getAdmin(): ?ItemInterface
    {
        $menu = $this->factory->createItem('themeAdminMenu');
        if (!$this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_READ)) {
            return null;
        }

        $menu->addChild('Bundle categories list', [
            'route' => 'zikulathemebundle_admin_view',
        ])->setAttribute('icon', 'fas fa-list');

        if ($this->localeApi->multilingual()) {
            $menu->addChild('Localization', [
                'uri' => '#'
            ])
                ->setAttribute('icon', 'fas fa-globe')
                ->setAttribute('dropdown', true)
            ;

            if ('dev' === $this->kernel->getEnvironment()) {
                $request = $this->requestStack->getCurrentRequest();
                if ($request->hasSession() && ($session = $request->getSession())) {
                    if ($session->has(EditInPlaceActivator::KEY)) {
                        $menu['Localization']->addChild('Disable edit in place', [
                            'route' => 'zikulathemebundle_localization_toggleeditinplace',
                        ])->setAttribute('icon', 'fas fa-ban');
                    } else {
                        $menu['Localization']->addChild('Enable edit in place', [
                            'route' => 'zikulathemebundle_localization_toggleeditinplace',
                        ])
                            ->setAttribute('icon', 'fas fa-user-edit')
                            ->setLinkAttribute('title', 'Edit translations directly in the context of a page')
                        ;
                    }
                }
                $menu['Localization']->addChild('Translation UI', [
                    'route' => 'translation_index',
                ])
                    ->setAttribute('icon', 'fas fa-language')
                    ->setLinkAttribute('title', 'Web interface to add, edit and remove translations')
                ;
            }
        }

        $menu->addChild('Test mail settings', [
            'route' => 'zikulathemebundle_tool_testmail',
        ])->setAttribute('icon', 'fas fa-envelope');

        $menu->addChild('PHP configuration', [
            'route' => 'zikulathemebundle_tool_phpinfo',
        ])->setAttribute('icon', 'fas fa-info-circle');

        $menu->addChild('Theme settings', [
            'route' => 'zikulathemebundle_config_config',
        ])->setAttribute('icon', 'fas fa-wrench');

        return 0 === $menu->count() ? null : $menu;
    }

    public function getBundleName(): string
    {
        return 'ZikulaThemeBundle';
    }
}
