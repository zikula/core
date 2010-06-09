<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */


class Categories_Api_Admin extends AbstractApi
{
    /**
     * get available admin panel links
     *
     * @author Mark West
     * @return array array of admin links
     */
    public function getlinks()
    {
        $links = array();

        if (SecurityUtil::checkPermission('Categories::', '::', ACCESS_READ)) {
            $links[] = array('url' => ModUtil::url('Categories', 'admin', 'view'), 'text' => $this->__('Categories list'), 'class' => 'z-icon-es-list');
        }
        if (SecurityUtil::checkPermission('Categories::', '::', ACCESS_ADD)) {
            $links[] = array('url' => ModUtil::url('Categories', 'admin', 'newcat'), 'text' => $this->__('Create new category'), 'class' => 'z-icon-es-new');
        }
        if (SecurityUtil::checkPermission('Categories::', '::', ACCESS_ADMIN)) {
            $links[] = array('url' => ModUtil::url('Categories', 'admin', 'editregistry'), 'text' => $this->__('Category registry'), 'class' => 'z-icon-es-cubes');
            $links[] = array('url' => ModUtil::url('Categories', 'admin', 'config'), 'text' => $this->__('Rebuild paths'), 'class' => 'z-icon-es-update');
            $links[] = array('url' => ModUtil::url('Categories', 'admin', 'preferences'), 'text' => $this->__('Settings'), 'class' => 'z-icon-es-config');
        }

        return $links;
    }
}