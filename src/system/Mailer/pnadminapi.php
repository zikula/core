<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @uses PHPMailer http://phpmailer.sourceforge.net
 * @package Zikula_System_Modules
 * @subpackage Mailer
 */

/**
 * Get available admin panel links.
 *
 * @author Mark West
 * @return array array of admin links
 */
function Mailer_adminapi_getlinks()
{
    $links = array();

    if (SecurityUtil::checkPermission('Mailer::', '::', ACCESS_ADMIN)) {
        $links[] = array('url' => pnModURL('Mailer', 'admin', 'testconfig'), 'text' => __('Test current settings'), 'class' => 'z-icon-es-mail');
    }
    if (SecurityUtil::checkPermission('Mailer::', '::', ACCESS_ADMIN)) {
        $links[] = array('url' => pnModURL('Mailer', 'admin', 'modifyconfig'), 'text' => __('Settings'), 'class' => 'z-icon-es-config');
    }

    return $links;
}
