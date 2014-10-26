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

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SiteOffListener implements EventSubscriberInterface
{

    public function onKernelRequestSiteOff(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        // Get variables
        $module = $request->attributes->get('_module');
        $type = $request->attributes->get('_type');
        $func = $request->attributes->get('_func');
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

            header('HTTP/1.1 503 Service Unavailable');
            require_once \System::getSystemErrorTemplate('siteoff.tpl');
            exit;
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(
                array('onKernelRequestSiteOff', 31),
            )
        );
    }

}
