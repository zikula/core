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

class Categories_Api_User extends Zikula_AbstractApi
{
    /**
     * get the roor category for a user
     *
     */
    public function getuserrootcat ($args)
    {
        $returnCategory = isset($args['returnCategory']) ? $args['returnCategory'] : false;
        $returnField    = isset($args['returnField'])    ? $args['returnField']    : 'id';

        $userRoot = $this->getVar('userrootcat', 0);
        if (!$userRoot) {
            return LogUtil::registerError($this->__('Error! Could not determine the user root node.'));
        }

        $userRootCat = CategoryUtil::getCategoryByPath ($userRoot);
        if (!$userRoot) {
            return LogUtil::registerError($this->__f('Error! The user root node seems to point towards an invalid category: %s.', $userRoot));
        }

        if ($userRootCat == 1) {
            return LogUtil::registerError($this->__("Error! The root directory cannot be modified in 'user' mode"));
        }

        $userCatName = $this->getusercategoryname ();
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
    public function getusercategories ($args)
    {
        $args['returnCategory'] = 1;
        $userRootCat = $this->getuserrootcat ($args);

        if (!$userRootCat) {
            return LogUtil::registerError($this->__f('Error! The user root node seems to point towards an invalid category: %s.', $userRoot));
        }

        $relative = (isset($args['relative']) ? $args['relative'] : false);

        return CategoryUtil::getCategoriesByParentID ($userRootCat['id'], '', $relative);
    }

    /**
     * get the username associated with a category
     *
     */
    public function getusercategoryname ($args)
    {
        $uid   = isset($args['uid']) && $args['uid'] ? $args['uid'] : UserUtil::getVar('uid');
        $uname = UserUtil::getVar('uname', $uid);
        $userCatName = "$uname [$uid]";

        return $userCatName;
    }

}
