<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Module\SearchModule\Listener;

use ModUtil;
use PageUtil;
use DataUtil;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\RouterInterface;
use System;
use SecurityUtil;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class FrontControllerListener implements EventSubscriberInterface
{
    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    public static function getSubscribedEvents()
    {
        return array(
            // Make sure to load the handler *every time* and *before* the routing listeners are running (32).
            KernelEvents::REQUEST => array(array('pageload', 40)),
        );
    }

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * Handle page load event KernelEvents::REQUEST.
     *
     * @param GetResponseEvent $event
     *
     * @return void
     */
    public function pageload(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        if (\System::isInstalling() || \System::isUpgrading()) {
            return;
        }
        $openSearchEnabled = ModUtil::getVar('ZikulaSearchModule', 'opensearch_enabled');
        if ($openSearchEnabled && SecurityUtil::checkPermission('ZikulaSearchModule::', '::', ACCESS_READ)) {
            // The current user has the rights to search the page.
            PageUtil::addVar('header', '<link rel="search" type="application/opensearchdescription+xml" title="' .
                DataUtil::formatForDisplay(System::getVar('sitename')) .
                '" href="' . DataUtil::formatForDisplay($this->router->generate('zikulasearchmodule_user_opensearch')) .
                '" />'
            );
        }
    }
}
