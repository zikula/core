<?php
/**
 * Zikula Application Framework
 *
 * @copyright Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @author Robert Gasch rgasch@gmail.com
 * @package Zikula_Core
 */

/**
 * get available admin panel links
 *
 * @author Mark West
 * @return array array of admin links
 */
function categories_adminapi_getlinks()
{
    $links = array();

    if (SecurityUtil::checkPermission('Categories::', '::', ACCESS_READ)) {
        $links[] = array('url' => pnModURL('Categories', 'admin', 'view'), 'text' => __('Categories list'));
    }
    if (SecurityUtil::checkPermission('Categories::', '::', ACCESS_ADD)) {
        $links[] = array('url' => pnModURL('Categories', 'admin', 'new'), 'text' => __('Create new category'));
    }
    if (SecurityUtil::checkPermission('Categories::', '::', ACCESS_ADMIN)) {
        $links[] = array('url' => pnModURL('Categories', 'admin', 'editregistry'), 'text' => __('Category registry'));
        $links[] = array('url' => pnModURL('Categories', 'admin', 'config'), 'text' => __('Rebuild paths'));
        $links[] = array('url' => pnModURL('Categories', 'admin', 'preferences'), 'text' => __('Settings'));
    }

    return $links;
}
