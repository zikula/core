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

namespace Zikula\SearchModule\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Bundle\CoreBundle\Site\SiteDefinitionInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\ThemeModule\Engine\AssetBag;

class FrontControllerListener implements EventSubscriberInterface
{
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
     * @var AssetBag
     */
    private $headerAssetBag;

    /**
     * @var SiteDefinitionInterface
     */
    private $site;

    /**
     * @var bool
     */
    private $installed;

    /**
     * @var bool
     */
    private $isUpgrading;

    public function __construct(
        RouterInterface $router,
        PermissionApiInterface $permissionApi,
        VariableApiInterface $variableApi,
        AssetBag $headerAssetBag,
        SiteDefinitionInterface $site,
        string $installed,
        $isUpgrading = false // cannot cast to bool because set with expression language
    ) {
        $this->router = $router;
        $this->permissionApi = $permissionApi;
        $this->variableApi = $variableApi;
        $this->headerAssetBag = $headerAssetBag;
        $this->site = $site;
        $this->installed = '0.0.0' !== $installed;
        $this->isUpgrading = $isUpgrading;
    }

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
     * Handle page load event KernelEvents::REQUEST.
     */
    public function pageLoad(RequestEvent $event): void
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
        $siteName = htmlspecialchars($this->site->getName(), ENT_QUOTES);
        $searchUrl = htmlentities($this->router->generate('zikulasearchmodule_search_opensearch'));

        $headerCode = '<link rel="search" type="' . $linkType . '" title="' . $siteName . '" href="' . $searchUrl . '" />';
        $this->headerAssetBag->add($headerCode);
    }
}
