<?php
/**
 * Copyright Zikula Foundation 2014 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\CoreBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\Core\Response\PlainResponse;

class SiteOffListener implements EventSubscriberInterface
{
    public function onKernelRequestSiteOff(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        $response = $event->getResponse();
        $request = $event->getRequest();
        if ($response instanceof PlainResponse
            || $response instanceof JsonResponse
            || $request->isXmlHttpRequest()) {
            return;
        }
        if (\System::isInstalling()) {
            return;
        }

        // Get variables
        $module = strtolower($request->query->get('module'));
        $type = strtolower($request->query->get('type'));
        $func = strtolower($request->query->get('func'));
        $siteOff = (bool)\System::getVar('siteoff');
        $hasAdminPerms = \SecurityUtil::checkPermission('ZikulaSettingsModule::', 'SiteOff::', ACCESS_ADMIN);
        $urlParams = ($module == 'users' && $type == 'user' && $func == 'siteofflogin'); // params are lowercase
        $versionCheck = (\Zikula_Core::VERSION_NUM != \System::getVar('Version_Num'));

        // Check for site closed
        if (($siteOff && !$hasAdminPerms && !$urlParams) || $versionCheck) {
            $hasOnlyOverviewAccess = \SecurityUtil::checkPermission('ZikulaUsersModule::', '::', ACCESS_OVERVIEW);
            if ($hasOnlyOverviewAccess && \UserUtil::isLoggedIn()) {
                \UserUtil::logout();
            }

            // initialise the language system to enable translations (#1764)
            $lang = \ZLanguage::getInstance();
            $lang->setup($request);

            $response = new Response();
            $response->headers->add(array('HTTP/1.1 503 Service Unavailable'));
            $response->setStatusCode(503);
            $content = require_once \System::getSystemErrorTemplate('siteoff.tpl'); // move to CoreBundle and use Twig
            $response->setContent($content);
            $event->setResponse($response);
            $event->stopPropagation();
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(
                array('onKernelRequestSiteOff', 200), // priority set high to catch request before other subscribers
            )
        );
    }
}
