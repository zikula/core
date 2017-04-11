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

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\ThemeModule\Engine\AssetBag;

class FrontControllerListener implements EventSubscriberInterface
{
    /**
     * @var \Symfony\Component\Routing\RouterInterface
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
     * @var AssetBag
     */
    private $headerAssetBag;

    /**
     * @var bool
     */
    private $installed;

    /**
     * @var bool
     */
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
     * @param RouterInterface $router RouterInterface service instance
     * @param PermissionApiInterface $permissionApi PermissionApi service instance
     * @param VariableApiInterface $variableApi VariableApi service instance
     * @param AssetBag $headerAssetBag AssetBag service instance for header code
     * @param bool $installed
     * @param bool $isUpgrading
     */
    public function __construct(
        RouterInterface $router,
        PermissionApiInterface $permissionApi,
        VariableApiInterface $variableApi,
        AssetBag $headerAssetBag,
        $installed,
        $isUpgrading = false
    ) {
        $this->router = $router;
        $this->permissionApi = $permissionApi;
        $this->variableApi = $variableApi;
        $this->headerAssetBag = $headerAssetBag;
        $this->installed = $installed;
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
        if (!$this->installed || $this->isUpgrading) {
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
        $siteName = htmlspecialchars($this->variableApi->getSystemVar('sitename'), ENT_QUOTES);
        $searchUrl = htmlentities($this->router->generate('zikulasearchmodule_search_opensearch'));

        $headerCode = '<link rel="search" type="' . $linkType . '" title="' . $siteName . '" href="' . $searchUrl . '" />';
        $this->headerAssetBag->add($headerCode);
    }
}
