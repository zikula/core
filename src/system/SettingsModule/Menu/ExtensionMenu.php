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

namespace Zikula\SettingsModule\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Translation\Bundle\EditInPlace\Activator as EditInPlaceActivator;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
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

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    public function __construct(
        FactoryInterface $factory,
        PermissionApiInterface $permissionApi,
        VariableApiInterface $variableApi,
        RequestStack $requestStack,
        ZikulaHttpKernelInterface $kernel
    ) {
        $this->factory = $factory;
        $this->permissionApi = $permissionApi;
        $this->variableApi = $variableApi;
        $this->requestStack = $requestStack;
        $this->kernel = $kernel;
    }

    public function get(string $type = self::TYPE_ADMIN): ?ItemInterface
    {
        if (self::TYPE_ADMIN === $type) {
            return $this->getAdmin();
        }

        return null;
    }

    private function getAdmin(): ?ItemInterface
    {
        if (!$this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADMIN)) {
            return null;
        }

        $menu = $this->factory->createItem('settingsAdminMenu');
        $menu->addChild('Main settings', [
            'route' => 'zikulasettingsmodule_settings_main',
        ])->setAttribute('icon', 'fas fa-wrench');

        $menu->addChild('Localisation', [
            'uri' => '#'
        ])
            ->setAttribute('icon', 'fas fa-globe')
            ->setAttribute('dropdown', true)
        ;

        $menu['Localisation']->addChild('Localisation settings', [
            'route' => 'zikulasettingsmodule_settings_locale',
        ])->setAttribute('icon', 'fas fa-spell-check');

        if (true === (bool)$this->variableApi->getSystemVar('multilingual')) {
            if ('dev' === $this->kernel->getEnvironment()) {
                $request = $this->requestStack->getCurrentRequest();
                if ($request->hasSession() && ($session = $request->getSession())) {
                    if ($session->has(EditInPlaceActivator::KEY)) {
                        $menu['Localisation']->addChild('Disable edit in place', [
                            'route' => 'zikulasettingsmodule_settings_toggleeditinplace',
                        ])->setAttribute('icon', 'fas fa-ban');
                    } else {
                        $menu['Localisation']->addChild('Enable edit in place', [
                            'route' => 'zikulasettingsmodule_settings_toggleeditinplace',
                        ])
                            ->setAttribute('icon', 'fas fa-user-edit')
                            ->setLinkAttribute('title', 'Edit translations directly in the context of a page')
                        ;
                    }
                }
                $menu['Localisation']->addChild('Translation UI', [
                    'route' => 'translation_index',
                ])
                    ->setAttribute('icon', 'fas fa-language')
                    ->setLinkAttribute('title', 'Web interface to add, edit and remove translations')
                ;
            }
        }

        $menu->addChild('PHP configuration', [
            'route' => 'zikulasettingsmodule_settings_phpinfo',
        ])->setAttribute('icon', 'fas fa-info-circle');

        return 0 === $menu->count() ? null : $menu;
    }

    public function getBundleName(): string
    {
        return 'ZikulaSettingsModule';
    }
}
