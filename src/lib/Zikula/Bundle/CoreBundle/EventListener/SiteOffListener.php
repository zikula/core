<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
            $response->headers->add(['HTTP/1.1 503 Service Unavailable']);
            $response->setStatusCode(503);
            $content = require_once \System::getSystemErrorTemplate('siteoff.tpl'); // move to CoreBundle and use Twig
            $response->setContent($content);
            $event->setResponse($response);
            $event->stopPropagation();
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['onKernelRequestSiteOff', 200] // priority set high to catch request before other subscribers
            ]
        ];
    }
}
