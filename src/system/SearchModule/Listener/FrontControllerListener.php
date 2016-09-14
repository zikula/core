<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SearchModule\Listener;

use DataUtil;
use System;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\PermissionsModule\Api\PermissionApi;
use Zikula\ThemeModule\Engine\AssetBag;

class FrontControllerListener implements EventSubscriberInterface
{
    /**
     * @var \Symfony\Component\Routing\RouterInterface
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
     * @var AssetBag
     */
    private $headerAssetBag;

    private $isUpgrading;

    public static function getSubscribedEvents()
    {
        return [
            // Make sure to load the handler *every time* and *before* the routing listeners are running (32).
            KernelEvents::REQUEST => [
                ['pageLoad', 40]
            ]
        ];
    }

    /**
     * FrontControllerListener constructor.
     *
     * @param RouterInterface $router         RouterInterface service instance
     * @param PermissionApi   $permissionApi  PermissionApi service instance
     * @param VariableApi     $variableApi    VariableApi service instance
     * @param AssetBag        $headerAssetBag AssetBag service instance for header code
     */
    public function __construct(RouterInterface $router, PermissionApi $permissionApi, VariableApi $variableApi, AssetBag $headerAssetBag, $isUpgrading = false)
    {
        $this->router = $router;
        $this->permissionApi = $permissionApi;
        $this->variableApi = $variableApi;
        $this->headerAssetBag = $headerAssetBag;
        $this->isUpgrading = $isUpgrading;
    }

    /**
     * Handle page load event KernelEvents::REQUEST.
     *
     * @param GetResponseEvent $event
     *
     * @return void
     */
    public function pageLoad(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        if (System::isInstalling() || $this->isUpgrading) {
            return;
        }
        $openSearchEnabled = $this->variableApi->get('ZikulaSearchModule', 'opensearch_enabled');
        if (!$openSearchEnabled) {
            return;
        }
        if (!$this->permissionApi->hasPermission('ZikulaSearchModule::', '::', ACCESS_READ)) {
            return;
        }

        // The current user has the rights to search the page.
        $linkType = 'application/opensearchdescription+xml';
        $siteName = DataUtil::formatForDisplay($this->variableApi->getSystemVar('sitename'));
        $searchUrl = DataUtil::formatForDisplay($this->router->generate('zikulasearchmodule_user_opensearch'));

        $headerCode = '<link rel="search" type="' . $linkType . '" title="' . $siteName . '" href="' . $searchUrl . '" />';
        $this->headerAssetBag->add($headerCode);
    }
}
