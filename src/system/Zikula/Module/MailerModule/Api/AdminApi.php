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

namespace Zikula\Module\MailerModule\Api;

use ModUtil;
use SecurityUtil;

/**
 * API functions used by administrative controllers
 */
class AdminApi extends \Zikula_AbstractApi
{
    const TYPE_MAIL = 1;
    const TYPE_SENDMAIL = 2;
    const TYPE_SMTP = 4;
    const TYPE_TEST = 5;

    static public $transportTypes = array(
        self::TYPE_MAIL => 'mail',
        self::TYPE_SENDMAIL => 'sendmail',
        self::TYPE_SMTP => 'smtp',
        self::TYPE_TEST => 'test',
    );

    /**
     * Get available admin panel links.
     *
     * @return array array of admin links
     */
    public function getlinks()
    {
        $links = array();

        if (SecurityUtil::checkPermission('ZikulaMailerModule::', '::', ACCESS_ADMIN)) {
            $links[] = array('url' => ModUtil::url('ZikulaMailerModule', 'admin', 'testconfig'), 'text' => $this->__('Test current settings'), 'icon' => 'envelope');
        }
        if (SecurityUtil::checkPermission('ZikulaMailerModule::', '::', ACCESS_ADMIN)) {
            $links[] = array('url' => ModUtil::url('ZikulaMailerModule', 'admin', 'modifyconfig'), 'text' => $this->__('Settings'), 'icon' => 'wrench');
        }

        return $links;
    }
}