<?php
/**
 * Copyright Zikula Foundation 2013 - Zikula Application Framework
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Search
 *          Please see the NOTICE file distributed with this source code for further
 *          information regarding copyright and licensing.
 */

namespace Zikula\Module\SearchModule\Listener;

use ModUtil;
use PageUtil;
use Zikula_Event;
use System;
use SecurityUtil;
use DataUtil;

/**
 * EventHandlers class.
 */
class PageloadListener
{
    /**
     * Handle page load event "frontcontroller.predispatch".
     *
     * @param Zikula_Event $event
     *
     * @return void
     */
    public static function pageload(Zikula_Event $event)
    {
        if (SecurityUtil::checkPermission('ZikulaSearchModule::', '::', ACCESS_READ)) {
            // The current user has the rights to search the page.
            PageUtil::addVar('header', '<link rel="search" type="application/opensearchdescription+xml" title="' . DataUtil::formatForDisplay(System::getVar('sitename')) . '" href="/' . DataUtil::formatForDisplay(ModUtil::url('ZikulaSearchModule', 'user', 'opensearch')) . '" />');
        }
    }
}
