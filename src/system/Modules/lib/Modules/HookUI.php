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
class Modules_HookUI
{
    public static function hookproviders(Zikula_Event $event)
    {
        // check if this is for this handler
        $subject = $event->getSubject();
        if (!($event['method'] == 'hookproviders' && strrpos(get_class($subject), '_Controller_Admin'))) {
           return;
        }

        $moduleName = $subject->getName();
        if (!SecurityUtil::checkPermission("$moduleName::", '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $view = Zikula_View::getInstance('Modules', false);
        $view->assign('currentmodule', $moduleName);

        // get all areas of the subscriber
        $subscriberAreas = HookUtil::getSubscriberAreasByOwner($moduleName);

        // get current sorting
        $currentSorting = array();
        foreach ($subscriberAreas as $subscriberArea) {
            $currentSorting[$subscriberArea] = array();
            $sortsByArea = HookUtil::getDisplaySortsByArea($subscriberArea);
            foreach ($sortsByArea as $sba) {
                array_push($currentSorting[$subscriberArea], $sba);
            }
        }

        $hookproviders = array();
        foreach ($currentSorting as $areaSorting) {
            foreach ($areaSorting as $sorting) {
                $provider = HookUtil::getOwnerByProviderArea($sorting);
                if (!array_key_exists($provider, $hookproviders) && ModUtil::available($provider)) {
                    $hookproviders[$provider] = ModUtil::getInfoFromName($provider);
                }
            }
        }
        $view->assign('hookproviders', $hookproviders);
        
        $event->setData($view->fetch('modules_hookui_providers.tpl'));
        $event->setNotified();
    }

    public static function hooksubscribers(Zikula_Event $event)
    {
        // check if this is for this handler
        $subject = $event->getSubject();
        if (!($event['method'] == 'hooksubscribers' && strrpos(get_class($subject), '_Controller_Admin'))) {
           return;
        }

        $moduleName = $subject->getName();
        if (!SecurityUtil::checkPermission($moduleName.'::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $view = Zikula_View::getInstance('Modules', false);
        $view->assign('currentmodule', $moduleName);

        $hooksubscribers = HookUtil::getHookSubscribers();
        $total_hooksubscribers = count($hooksubscribers);
        for ($i=0 ; $i < $total_hooksubscribers ; $i++) {
            if ($hooksubscribers[$i]['name'] == $moduleName) {
                unset($hooksubscribers[$i]);
                continue;
            }
            $hooksubscribers[$i]['attached'] = (count(HookUtil::bindingsBetweenProviderAndSubscriber($hooksubscribers[$i]['name'], $moduleName)) > 0) ? true : false;
        }
        $view->assign('hooksubscribers', $hooksubscribers);

        $event->setData($view->fetch('modules_hookui_subscribers.tpl'));
        $event->setNotified();
    }

    /**
     * populate Services menu with hook links if capable
     * 
     * @param Zikula_Event $event
     */
    public static function servicelinks(Zikula_Event $event)
    {
        $module = $event->getArg('modname');

        if (HookUtil::isSubscriberCapable($module)) {
            $event->data[] = array('url' => ModUtil::url($module, 'admin', 'hookproviders'), 'text' => __('Hook Providers'));
        }

        if (HookUtil::isProviderCapable($module)) {
            $event->data[] = array('url' => ModUtil::url($module, 'admin', 'hooksubscribers'), 'text' => __('Hook Subscribers'));
        }
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

        $view = Zikula_View::getInstance('Modules', false);
        $view->assign('currentmodule', $moduleName);

        // notify EVENT here to gather any system service links
        $localevent = new Zikula_Event('module_dispatch.service_links', $subject, array('modname' => $moduleName));
        EventUtil::notify($localevent);
        $sublinks = $localevent->getData();
        $view->assign('sublinks', $sublinks);

        $event->setData($view->fetch('modules_hookui_moduleservices.tpl'));
        $event->setNotified();
    }
}
