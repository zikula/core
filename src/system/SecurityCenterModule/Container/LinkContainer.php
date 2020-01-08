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

namespace Zikula\SecurityCenterModule\Container;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Core\LinkContainer\LinkContainerInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
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

    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    public function __construct(
        TranslatorInterface $translator,
        RouterInterface $router,
        PermissionApiInterface $permissionApi,
        VariableApiInterface $variableApi
    ) {
        $this->translator = $translator;
        $this->router = $router;
        $this->permissionApi = $permissionApi;
        $this->variableApi = $variableApi;
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
            'url' => $this->router->generate('zikulasecuritycentermodule_config_config'),
            'text' => $this->translator->trans('Settings'),
            'icon' => 'wrench'
        ];
        $links[] = [
            'url' => $this->router->generate('zikulasecuritycentermodule_config_allowedhtml'),
            'text' => $this->translator->trans('Allowed HTML settings'),
            'icon' => 'list'
        ];
        $links[] = [
            'url' => $this->router->generate('zikulasecuritycentermodule_idslog_view'),
            'text' => $this->translator->trans('View IDS log'),
            'icon' => 'align-justify',
            'links' => [
                [
                    'url' => $this->router->generate('zikulasecuritycentermodule_idslog_view'),
                    'text' => $this->translator->trans('View IDS log')
                ],
                [
                    'url' => $this->router->generate('zikulasecuritycentermodule_idslog_export'),
                    'text' => $this->translator->trans('Export IDS log')
                ],
                [
                    'url' => $this->router->generate('zikulasecuritycentermodule_idslog_purge'),
                    'text' => $this->translator->trans('Purge IDS log')
                ]
            ]
        ];

        $outputfilter = $this->variableApi->getSystemVar('outputfilter');
        if (1 === $outputfilter) {
            $links[] = [
                'url' => $this->router->generate('zikulasecuritycentermodule_config_purifierconfig'),
                'text' => $this->translator->trans('HTMLPurifier settings'),
                'icon' => 'wrench'
            ];
        }

        return $links;
    }

    public function getBundleName(): string
    {
        return 'ZikulaSecurityCenterModule';
    }
}
