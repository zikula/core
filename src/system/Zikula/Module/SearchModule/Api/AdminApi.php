<?php
/**
 * Copyright Zikula Foundation 2011 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Module\SearchModule\Api;

use ModUtil;
use SecurityUtil;

/**
 * Search_Api_Admin class.
 */
class AdminApi extends \Zikula_AbstractApi
{
    /**
     * Get available admin panel links.
     *
     * @return array array of admin links
     */
    public function getlinks()
    {
        $links = array();

        if (SecurityUtil::checkPermission('ZikulaSearchModule::', '::', ACCESS_ADMIN)) {
            $links[] = array('url' => ModUtil::url('ZikulaSearchModule', 'admin', 'modifyconfig'), 'text' => $this->__('Settings'), 'class' => 'z-icon-es-config');
        }

        return $links;
    }

}