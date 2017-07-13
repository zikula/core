<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SecurityCenterModule\Container;

use Symfony\Component\Routing\RouterInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Core\LinkContainer\LinkContainerInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
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

    /**
     * LinkContainer constructor.
     *
     * @param TranslatorInterface $translator    TranslatorInterface service instance
     * @param RouterInterface     $router        RouterInterface service instance
     * @param PermissionApiInterface $permissionApi PermissionApi service instance
     * @param VariableApiInterface $variableApi   VariableApi service instance
     */
    public function __construct(TranslatorInterface $translator, RouterInterface $router, PermissionApiInterface $permissionApi, VariableApiInterface $variableApi)
    {
        $this->translator = $translator;
        $this->router = $router;
        $this->permissionApi = $permissionApi;
        $this->variableApi = $variableApi;
    }

    /**
     * get Links of any type for this extension
     * required by the interface
     *
     * @param string $type
     * @return array
     */
    public function getLinks($type = LinkContainerInterface::TYPE_ADMIN)
    {
        if (LinkContainerInterface::TYPE_ADMIN == $type) {
            return $this->getAdmin();
        }

        return [];
    }

    /**
     * get the Admin links for this extension
     *
     * @return array
     */
    private function getAdmin()
    {
        $links = [];

        if (!$this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADMIN)) {
            return $links;
        }

        $links[] = [
            'url' => $this->router->generate('zikulasecuritycentermodule_config_config'),
            'text' => $this->translator->__('Settings'),
            'icon' => 'wrench'
        ];
        $links[] = [
            'url' => $this->router->generate('zikulasecuritycentermodule_config_allowedhtml'),
            'text' => $this->translator->__('Allowed HTML settings'),
            'icon' => 'list'
        ];
        $links[] = [
            'url' => $this->router->generate('zikulasecuritycentermodule_idslog_view'),
            'text' => $this->translator->__('View IDS log'),
            'icon' => 'align-justify',
            'links' => [
                [
                    'url' => $this->router->generate('zikulasecuritycentermodule_idslog_view'),
                    'text' => $this->translator->__('View IDS log')
                ],
                [
                    'url' => $this->router->generate('zikulasecuritycentermodule_idslog_export'),
                    'text' => $this->translator->__('Export IDS log')
                ],
                [
                    'url' => $this->router->generate('zikulasecuritycentermodule_idslog_purge'),
                    'text' => $this->translator->__('Purge IDS log')
                ]
            ]
        ];

        $outputfilter = $this->variableApi->getSystemVar('outputfilter');
        if ($outputfilter == 1) {
            $links[] = [
                'url' => $this->router->generate('zikulasecuritycentermodule_config_purifierconfig'),
                'text' => $this->translator->__('HTMLPurifier settings'),
                'icon' => 'wrench'
            ];
        }

        return $links;
    }

    /**
     * set the BundleName as required by the interface
     *
     * @return string
     */
    public function getBundleName()
    {
        return 'ZikulaSecurityCenterModule';
    }
}
