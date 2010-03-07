<?php
/**
 * Zikula Application Framework
 *
 * @copyright Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id: pnuser.php 22343 2007-07-06 14:57:30Z rgasch $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @author Robert Gasch rgasch@gmail.com
 * @package Zikula_Core
 */

/**
 * get the roor category for a user
 *
 */
function Categories_userapi_getuserrootcat ($args)
{
    $returnCategory = isset($args['returnCategory']) ? $args['returnCategory'] : false;
    $returnField    = isset($args['returnField'])    ? $args['returnField']    : 'id';

    $userRoot = pnModGetVar ('Categories', 'userrootcat', 0);
    if (!$userRoot) {
        return LogUtil::registerError(__('Error! Could not determine the user root node.'));
    }

    Loader::loadClass ('CategoryUtil');
    $userRootCat = CategoryUtil::getCategoryByPath ($userRoot);
    if (!$userRoot) {
        return LogUtil::registerError(__f('Error! The user root node seems to point towards an invalid category: %s.', $userRoot));
    }

    if ($userRootCat == 1) {
        return LogUtil::registerError(__("Error! The root directory cannot be modified in 'user' mode"));
    }

    $userCatName = Categories_userapi_getusercategoryname ();
    $thisUserRootCatPath = $userRoot . '/' . $userCatName;
    $thisUserRootCat = CategoryUtil::getCategoryByPath ($thisUserRootCatPath);

    if (!$thisUserRootCat) {
        return false;
    }

    if ($returnCategory) {
        return $thisUserRootCat;
    }

    return $thisUserRootCat[$returnField];
}

/**
 * get all categories for a user
 *
 */
function Categories_userapi_getusercategories ($args)
{
    $args['returnCategory'] = 1;
    $userRootCat = Categories_userapi_getuserrootcat ($args);

    if (!$userRootCat) {
        return LogUtil::registerError(__f('Error! The user root node seems to point towards an invalid category: %s.', $userRoot));
    }

    $relative = (isset($args['relative']) ? $args['relative'] : false);
    return CategoryUtil::getCategoriesByParentID ($userRootCat['id'], '', $relative);
}

/**
 * get the username associated with a category
 *
 */
function Categories_userapi_getusercategoryname ($args)
{
    $uid   = isset($args['uid']) && $args['uid'] ? $args['uid'] : pnUserGetVar('uid');
    $uname = pnUserGetVar('uname', $uid);
    $userCatName = "$uname [$uid]";

    return $userCatName;
}

