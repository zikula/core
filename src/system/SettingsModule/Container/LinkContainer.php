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

namespace Zikula\SettingsModule\Container;

use Symfony\Component\Routing\RouterInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Core\LinkContainer\LinkContainerInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

class LinkContainer implements LinkContainerInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    public function __construct(
        TranslatorInterface $translator,
        RouterInterface $router,
        PermissionApiInterface $permissionApi
    ) {
        $this->translator = $translator;
        $this->router = $router;
        $this->permissionApi = $permissionApi;
    }

    public function getLinks(string $type = LinkContainerInterface::TYPE_ADMIN): array
    {
        if (LinkContainerInterface::TYPE_ADMIN === $type) {
            return $this->getAdmin();
        }

        return [];
    }

    /**
     * Get the admin links for this extension.
     */
    private function getAdmin(): array
    {
        $links = [];

        if (!$this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADMIN)) {
            return $links;
        }

        $links[] = [
            'url' => $this->router->generate('zikulasettingsmodule_settings_main'),
            'text' => $this->translator->trans('Main settings'),
            'icon' => 'wrench'
        ];
        $links[] = [
            'url' => $this->router->generate('zikulasettingsmodule_settings_locale'),
            'text' => $this->translator->trans('Localisation settings'),
            'icon' => 'globe'
        ];
        $links[] = [
            'url' => $this->router->generate('zikulasettingsmodule_settings_phpinfo'),
            'text' => $this->translator->trans('PHP configuration'),
            'icon' => 'info-circle'
        ];

        return $links;
    }

    public function getBundleName(): string
    {
        return 'ZikulaSettingsModule';
    }
}
