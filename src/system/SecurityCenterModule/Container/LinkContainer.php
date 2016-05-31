<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SecurityCenterModule\Container;

use Symfony\Component\Routing\RouterInterface;
use Zikula\Common\Translator\Translator;
use Zikula\Core\LinkContainer\LinkContainerInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\PermissionsModule\Api\PermissionApi;

class LinkContainer implements LinkContainerInterface
{
    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var PermissionApi
     */
    private $permissionApi;

    /**
     * @var VariableApi
     */
    private $variableApi;

    /**
     * LinkContainer constructor.
     *
     * @param Translator      $translator    Translator service instance.
     * @param RouterInterface $router        RouterInterface service instance.
     * @param PermissionApi   $permissionApi PermissionApi service instance.
     * @param VariableApi     $variableApi   VariableApi service instance.
     */
    public function __construct($translator, RouterInterface $router, PermissionApi $permissionApi, VariableApi $variableApi)
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
        $method = 'get' . ucfirst(strtolower($type));
        if (method_exists($this, $method)) {
            return $this->$method();
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

        if (!$this->permissionApi->hasPermission('ZikulaSecurityCenterModule::', '::', ACCESS_ADMIN)) {
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
            'text' => $this->translator->__('View IDS Log'),
            'icon' => 'align-justify',
            'links' => [
                [
                    'url' => $this->router->generate('zikulasecuritycentermodule_idslog_view'),
                    'text' => $this->translator->__('View IDS Log')
                ],
                [
                    'url' => $this->router->generate('zikulasecuritycentermodule_idslog_export'),
                    'text' => $this->translator->__('Export IDS Log')
                ],
                [
                    'url' => $this->router->generate('zikulasecuritycentermodule_idslog_purge'),
                    'text' => $this->translator->__('Purge IDS Log')
                ]
            ]
        ];

        $outputfilter = $this->variableApi->get(VariableApi::CONFIG, 'outputfilter');
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
