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

class Mailer_Api_Admin extends Zikula_Api
{
    /**
     * Get available admin panel links.
     *
     * @author Mark West
     * @return array array of admin links
     */
    function getlinks()
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