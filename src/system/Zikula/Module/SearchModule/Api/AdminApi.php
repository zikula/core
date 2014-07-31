<?php
/**
 * Copyright Zikula Foundation 2011 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Module\SearchModule\Api;

use ModUtil;
use SecurityUtil;

/**
 * API's used by administrative controllers
 */
class AdminApi extends \Zikula_AbstractApi
{
    /**
     * Get available admin panel links.
     *
     * @return array array of admin links
     */
    public function getLinks()
    {
        $links = array();

        $links[] = array('url' => $this->get('router')->generate('zikulasearchmodule_user_form'), 'text' => $this->__('Frontend'), 'icon' => 'search');

        if (SecurityUtil::checkPermission('ZikulaSearchModule::', '::', ACCESS_ADMIN)) {
            $links[] = array('url' => $this->get('router')->generate('zikulasearchmodule_admin_modifyconfig'), 'text' => $this->__('Settings'), 'icon' => 'wrench');
        }

        return $links;
    }
}
