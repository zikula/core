<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Api;

use CategoryUtil;
use UserUtil;

/**
 * User api functions for the categories module
 */
class UserApi extends \Zikula_AbstractApi
{
    /**
     * get the root category for a user
     *
     * @param mixed[] $args {
     *      @type  int     $returnCategory
     *      @type  string  $returnField
     *                       }
     *
     * @return string|array|bool the return field if returnCategory is false, the full category if returnCategory is true, false otherwise
     *
     * @throws \RuntimeException Thrown if the user root points to an invalid category or
     *                                  if the root user root points to the system root category
     */
    public function getuserrootcat($args)
    {
        $returnCategory = isset($args['returnCategory']) ? $args['returnCategory'] : false;
        $returnField    = isset($args['returnField'])    ? $args['returnField']    : 'id';

        $userRoot = $this->getVar('userrootcat', 0);
        if (!$userRoot) {
            throw new \RuntimeException($this->__f('Error! The user root node seems to point towards an invalid category: %s.', $userRoot));
        }

        $userRootCat = CategoryUtil::getCategoryByPath($userRoot);
        if ($userRootCat == 1) {
            throw new \RuntimeException($this->__("Error! The root directory cannot be modified in 'user' mode"));
        }

        $userCatName = $this->getusercategoryname([]);
        $thisUserRootCatPath = $userRoot . '/' . $userCatName;
        $thisUserRootCat = CategoryUtil::getCategoryByPath($thisUserRootCatPath);

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
     * @param mixed[] $args {
     *      @type bool $relative optionally generate relative paths
     *                       }
     *
     * @return array array of categories
     *
     * @throws \RuntimeException Thrown if the user root points to an invalid category
     */
    public function getusercategories($args)
    {
        $args['returnCategory'] = 1;
        $userRootCat = $this->getuserrootcat($args);

        if (!$userRootCat) {
            throw new \RuntimeException($this->__('Error! The user root node seems to point towards an invalid category.'));
        }

        $relative = (isset($args['relative']) ? $args['relative'] : false);

        return CategoryUtil::getCategoriesByParentID($userRootCat['id'], '', $relative);
    }

    /**
     * get the username associated with a category
     *
     * @param mixed[] $args {
     *      @type int $uid the user id
     *                       }
     *
     * @return string the username associated with the category
     */
    public function getusercategoryname($args)
    {
        $uid   = isset($args['uid']) && $args['uid'] ? $args['uid'] : UserUtil::getVar('uid');
        $uname = UserUtil::getVar('uname', $uid);
        $userCatName = "$uname [$uid]";

        return $userCatName;
    }
}
