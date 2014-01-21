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
use System;
use SecurityUtil;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\Core\Event\GenericEvent;

class FrontControllerListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'frontcontroller.predispatch' => array('pageload'),
        );
    }

    /**
     * Handle page load event "frontcontroller.predispatch".
     *
     * @param GenericEvent $event
     *
     * @return void
     */
    public function pageload(GenericEvent $event)
    {
        if (SecurityUtil::checkPermission('ZikulaSearchModule::', '::', ACCESS_READ)) {
            // The current user has the rights to search the page.
            PageUtil::addVar('header', '<link rel="search" type="application/opensearchdescription+xml" title="' . DataUtil::formatForDisplay(System::getVar('sitename')) . '" href="/' . DataUtil::formatForDisplay(ModUtil::url('ZikulaSearchModule', 'user', 'opensearch')) . '" />');
        }
    }

}