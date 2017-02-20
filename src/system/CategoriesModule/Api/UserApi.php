<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Api;

use ServiceUtil;

/**
 * User api functions for the categories module.
 *
 * @deprecated remove at Core-2.0
 */
class UserApi extends \Zikula_AbstractApi
{
    /**
     * get the root category for a user
     *
     * @param mixed[] $args {
     *      @type  bool   $returnCategory Whether the whole category object should be returned or not
     *      @type  string $returnField    Name of field to return if $returnCategory is false
     * }
     *
     * @return string|array|bool the return field if returnCategory is false, the full category if returnCategory is true, false otherwise
     *
     * @throws \RuntimeException Thrown if the user root points to an invalid category or
     *                                  if the root user root points to the system root category
     */
    public function getuserrootcat($args)
    {
        @trigger_error('Categories UserApi is deprecated. please use the new user categories api instead.', E_USER_DEPRECATED);

        $returnCategory = isset($args['returnCategory']) ? (bool)$args['returnCategory'] : false;
        $returnField    = isset($args['returnField']) ? $args['returnField'] : 'id';
        unset($args);

        return ServiceUtil::get('zikula_categories_module.api.user_categories')->getUserRootCategory($returnCategory, $returnField);
    }

    /**
     * get all categories for a user
     *
     * @param mixed[] $args {
     *      @type bool $relative optionally generate relative paths
     * }
     *
     * @return array array of categories
     *
     * @throws \RuntimeException Thrown if the user root points to an invalid category
     */
    public function getusercategories($args)
    {
        @trigger_error('Categories UserApi is deprecated. please use the new user categories api instead.', E_USER_DEPRECATED);

        $relative = isset($args['relative']) ? (bool)$args['relative'] : false;

        return ServiceUtil::get('zikula_categories_module.api.user_categories')->getUserCategories($relative);
    }

    /**
     * get the username associated with a category
     *
     * @param mixed[] $args {
     *      @type int $uid the user id
     * }
     *
     * @return string Root category name based on the username
     */
    public function getusercategoryname($args)
    {
        @trigger_error('Categories UserApi is deprecated. please use the new user categories api instead.', E_USER_DEPRECATED);

        $uid = isset($args['uid']) && $args['uid'] ? $args['uid'] : 0;

        return ServiceUtil::get('zikula_categories_module.api.user_categories')->getUserCategoryName($uid);
    }
}
