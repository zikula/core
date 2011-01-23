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

/**
 * HooksUI class.
 */
class Extensions_HookUI
{
    public static function hooks(Zikula_Event $event)
    {
        // check if this is for this handler
        $subject = $event->getSubject();
        if (!($event['method'] == 'hooks' && strrpos(get_class($subject), '_Controller_Admin'))) {
           return;
        }

        $moduleName = $subject->getName();
        if (!SecurityUtil::checkPermission("$moduleName::", '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $view = Zikula_View::getInstance('Extensions', false);
        $view->assign('currentmodule', $moduleName);

        // find out the capabilities of the module
        $isProvider = (HookUtil::isProviderCapable($moduleName)) ? true : false;
        $view->assign('isProvider', $isProvider);

        $isSubscriber = (HookUtil::isSubscriberCapable($moduleName)) ? true : false;
        $view->assign('isSubscriber', $isSubscriber);

        // get areas of module
        if ($isProvider) {
            $providerAreas = HookUtil::getProviderAreasByOwner($moduleName);
            $view->assign('providerAreas', $providerAreas);
        }

        if ($isSubscriber) {
            $subscriberAreas = HookUtil::getSubscriberAreasByOwner($moduleName);
            $view->assign('subscriberAreas', $subscriberAreas);
        }

        // get available subscribers for provider
        if ($isProvider) {
            $hooksubscribers = HookUtil::getHookSubscribers();
            $total_hooksubscribers = count($hooksubscribers);
            for ($i=0 ; $i < $total_hooksubscribers ; $i++) {
                if ($hooksubscribers[$i]['name'] == $moduleName) {
                    unset($hooksubscribers[$i]);
                    continue;
                }

                if (!SecurityUtil::checkPermission($hooksubscribers[$i]['name']."::", '::', ACCESS_ADMIN)) {
                    unset($hooksubscribers[$i]);
                    continue;
                }

                // get areas of subscriber
                $subscriberAreas = HookUtil::getSubscriberAreasByOwner($hooksubscribers[$i]['name']);
                $hooksubscribers[$i]['areas'] = $subscriberAreas;
            }
            $view->assign('hooksubscribers', $hooksubscribers);
        }

        // get providers for subscriber
        if ($isSubscriber) {
            // get current sorting
            $currentSorting = array();
            for ($i=0 ; $i < count($subscriberAreas) ; $i++) {
                $currentSorting[$subscriberAreas[$i]] = array();
                $sortsByArea = HookUtil::getDisplaySortsByArea($subscriberAreas[$i]);
                foreach ($sortsByArea as $sba) {
                    array_push($currentSorting[$subscriberAreas[$i]], $sba);
                }
            }
            $view->assign('areasSorting', $currentSorting);
        }
        
        $event->setData($view->fetch('extensions_hookui_hooks.tpl'));
        $event->setNotified();
    } 

    public static function moduleservices(Zikula_Event $event)
    {
        // check if this is for this handler
        $subject = $event->getSubject();
        if (!($event['method'] == 'moduleservices' && strrpos(get_class($subject), '_Controller_Admin'))) {
           return;
        }

        $moduleName = $subject->getName();
        if (!SecurityUtil::checkPermission($moduleName.'::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $view = Zikula_View::getInstance('Extensions', false);
        $view->assign('currentmodule', $moduleName);

        // notify EVENT here to gather any system service links
        $localevent = new Zikula_Event('module_dispatch.service_links', $subject, array('modname' => $moduleName));
        EventUtil::notify($localevent);
        $sublinks = $localevent->getData();
        $view->assign('sublinks', $sublinks);

        $event->setData($view->fetch('extensions_hookui_moduleservices.tpl'));
        $event->setNotified();
    }
}
