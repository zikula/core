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
        $links[] = array('url' => ModUtil::url('Categories', 'admin', 'view'), 'text' => __('Categories list'), 'class' => 'z-icon-es-list');
    }
    if (SecurityUtil::checkPermission('Categories::', '::', ACCESS_ADD)) {
        $links[] = array('url' => ModUtil::url('Categories', 'admin', 'new'), 'text' => __('Create new category'), 'class' => 'z-icon-es-new');
    }
    if (SecurityUtil::checkPermission('Categories::', '::', ACCESS_ADMIN)) {
        $links[] = array('url' => ModUtil::url('Categories', 'admin', 'editregistry'), 'text' => __('Category registry'), 'class' => 'z-icon-es-cubes');
        $links[] = array('url' => ModUtil::url('Categories', 'admin', 'config'), 'text' => __('Rebuild paths'), 'class' => 'z-icon-es-update');
        $links[] = array('url' => ModUtil::url('Categories', 'admin', 'preferences'), 'text' => __('Settings'), 'class' => 'z-icon-es-config');
    }

    return $links;
}
