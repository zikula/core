<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
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

/**
 * Mailer_Api_Admin class.
 */
class Mailer_Api_Admin extends Zikula_AbstractApi
{
    /**
     * Get available admin panel links.
     *
     * @return array array of admin links
     */
    public function getlinks()
    {
        $links = array();

        if (SecurityUtil::checkPermission('Mailer::', '::', ACCESS_ADMIN)) {
            $links[] = array('url' => ModUtil::url('Mailer', 'admin', 'testconfig'), 'text' => $this->__('Test current settings'), 'class' => 'z-icon-es-mail');
        }
        if (SecurityUtil::checkPermission('Mailer::', '::', ACCESS_ADMIN)) {
            $links[] = array('url' => ModUtil::url('Mailer', 'admin', 'modifyconfig'), 'text' => $this->__('Settings'), 'class' => 'z-icon-es-config');
        }

        return $links;
    }

}
