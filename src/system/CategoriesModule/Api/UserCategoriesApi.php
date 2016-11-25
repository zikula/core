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

use UserUtil;
use Zikula\CategoriesModule\Api\CategoryApi;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\UsersModule\Api\CurrentUserApi;

/**
 * UserCategoriesApi
 */
class UserCategoriesApi
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var CategoryApi
     */
    private $categoryApi;

    /**
     * @var CurrentUserApi
     */
    private $currentUserApi;

    /**
     * UserCategoriesApi constructor.
     *
     * @param TranslatorInterface $translator     TranslatorInterface service instance
     * @param CategoryApi         $categoryApi    CategoryApi service instance
     * @param CurrentUserApi      $currentUserApi CurrentUserApi service instance
     */
    public function __construct(TranslatorInterface $translator, CategoryApi $categoryApi, CurrentUserApi $currentUserApi)
    {
        $this->translator = $translator;
        $this->categoryApi = $categoryApi;
        $this->currentUserApi = $currentUserApi;
    }

    /**
     * get the root category for a user
     *
     * @param bool   $returnCategory Whether the whole category object should be returned or not
     * @param string $returnField    Name of field to return if $returnCategory is false
     *
     * @return string|array|bool the return field if returnCategory is false, the full category if returnCategory is true, false otherwise
     *
     * @throws \RuntimeException Thrown if the user root points to an invalid category or
     *                                  if the root user root points to the system root category
     */
    public function getUserRootCategory($returnCategory = false, $returnField = 'id')
    {
        $userRoot = $this->getVar('userrootcat', 0);
        if (!$userRoot) {
            throw new \RuntimeException($this->translator->__f('Error! The user root node seems to point towards an invalid category: %s.', ['%s' => $userRoot]));
        }

        $userRootCat = $this->categoryApi->getCategoryByPath($userRoot);
        if ($userRootCat == 1) {
            throw new \RuntimeException($this->translator->__("Error! The root directory cannot be modified in 'user' mode"));
        }

        $userCatName = $this->getusercategoryname([]);
        $thisUserRootCatPath = $userRoot . '/' . $userCatName;
        $thisUserRootCat = $this->categoryApi->getCategoryByPath($thisUserRootCatPath);

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
     * @param bool $relative optionally generate relative paths
     *
     * @return array array of categories
     *
     * @throws \RuntimeException Thrown if the user root points to an invalid category
     */
    public function getUserCategories($relative = false)
    {
        $userRootCat = $this->getUserRootCategory(true);

        if (!$userRootCat) {
            throw new \RuntimeException($this->translator->__('Error! The user root node seems to point towards an invalid category.'));
        }

        return $this->categoryApi->getCategoriesByParentId($userRootCat['id'], '', $relative);
    }

    /**
     * get the username associated with a category
     *
     * @param int $userId The user id (optional)
     *
     * @return string Root category name based on the username
     */
    public function getUserCategoryName($userId = 0)
    {
        if (!is_numeric($userId) || $userId < 1) {
            $userId = $this->currentUserApi->get('uid');
        }

        $userName = UserUtil::getVar('uname', $userId);
        $userCatName = $userName . ' [' . $userId . ']';

        return $userCatName;
    }
}
