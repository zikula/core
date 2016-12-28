<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Helper;

use Doctrine\ORM\EntityManager;
use Zikula\CategoriesModule\Api\CategoryApi;
use Zikula\CategoriesModule\Entity\CategoryEntity;
use Zikula\Common\Translator\TranslatorInterface;

/**
 * Category copying and moving helper functions for the categories module.
 */
class CopyAndMoveHelper
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var CategoryApi
     */
    private $categoryApi;

    /**
     * @var PathBuilderHelper
     */
    private $pathBuilder;

    /**
     * CopyAndMoveHelper constructor.
     *
     * @param TranslatorInterface $translator    TranslatorInterface service instance
     * @param EntityManager       $entityManager EntityManager service instance
     * @param CategoryApi         $categoryApi   CategoryApi service instance
     * @param PathBuilderHelper   $pathBuilder   PathBuilderHelper service instance
     */
    public function __construct(TranslatorInterface $translator, EntityManager $entityManager, CategoryApi $categoryApi, PathBuilderHelper $pathBuilder)
    {
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->categoryApi = $categoryApi;
        $this->pathBuilder = $pathBuilder;
    }

    /**
     * Move categories by ID (recursive move).
     *
     * @param integer $cid         The categoryID we wish to move
     * @param integer $newParentId The categoryID of the new parent category
     *
     * @return boolean
     */
    public function moveCategoriesById($cid, $newParentId)
    {
        if (!$cid) {
            return false;
        }

        $cat = $this->categoryApi->getCategoryById($cid);
        if (!$cat) {
            return false;
        }

        return $this->moveCategoriesByPath($cat['ipath'], $newParentId);
    }

    /**
     * Move Categories by path (recursive move).
     *
     * @param string  $apath       The path to move from
     * @param integer $newParentId The categoryID of the new parent category
     * @param string  $pathField   The field to use for the path reference (optional) (default='ipath')
     * @param boolean $includeRoot Whether or not to also move the root folder  (optional) (default=true)
     *
     * @return boolean
     */
    public function moveCategoriesByPath($apath, $newParentId, $pathField = 'ipath', $includeRoot = true)
    {
        if (!$apath) {
            return false;
        }

        $cats = $this->categoryApi->getCategoriesByPath($apath, 'path', $pathField);
        $newParent = $this->categoryApi->getCategoryByID($newParentId);

        if (!$newParent || !$cats) {
            return false;
        }

        $newParentIPath = $newParent['ipath'] . '/';
        $newParentPath = $newParent['path'] . '/';

        $oldParent = $this->categoryApi->getCategoryByID($cats[0]['parent_id']);
        $oldParentIPath = $oldParent['ipath'] . '/';
        $oldParentPath = $oldParent['path'] . '/';

        $categoryRepository = $this->entityManager->getRepository('ZikulaCategoriesModule:CategoryEntity');
        $categories = $categoryRepository->getCategoriesInPath($pathField, $apath);

        $fpath = 'path';
        $fipath = 'ipath';
        foreach ($categories as $category) {
            $category[$fpath] = mb_ereg_replace($oldParentPath, $newParentPath, $category[$fpath]);
            $category[$fipath] = mb_ereg_replace($oldParentIPath, $newParentIPath, $category[$fipath]);
        }

        $this->entityManager->flush();

        $pid = $cats[0]['id'];

        $categoryRepository->updateParent($pid, $newParentId, $includeRoot);

        return true;
    }

    /**
     * Move SubCategories by path (recursive move).
     *
     * @param string  $apath       The path to move from
     * @param integer $newParentId The categoryID of the new parent category
     * @param string  $pathField   The field to use for the path reference (optional) (default='ipath')
     *
     * @return boolean
     */
    public function moveSubCategoriesByPath($apath, $newParentId, $pathField = 'ipath')
    {
        return $this->moveCategoriesByPath($apath, $newParentId, $pathField, false);
    }

    /**
     * Copy categories by ID (recursive copy).
     *
     * @param integer $cid         The categoryID we wish to copy
     * @param integer $newParentId The categoryID of the new parent category
     *
     * @return boolean
     */
    public function copyCategoriesById($cid, $newParentId)
    {
        $cat = $this->categoryApi->getCategoryById($cid);

        if (!$cat) {
            return false;
        }

        return $this->copyCategoriesByPath($cat['ipath'], $newParentId);
    }

    /**
     * Copy categories by path (recursive copy).
     *
     * @param string  $apath       The path to copy from
     * @param integer $newParentId The categoryID of the new parent category
     * @param string  $pathField   The field to use for the path reference (optional) (default='ipath')
     * @param boolean $includeRoot Whether or not to also move the root folder (optional) (default=true)
     *
     * @return boolean
     */
    public function copyCategoriesByPath($apath, $newParentId, $pathField = 'ipath', $includeRoot = true)
    {
        if (!$apath || !$newParentId) {
            return false;
        }

        $cats = $this->categoryApi->getSubCategoriesByPath($apath, 'ipath', $pathField, true, true);
        $newParentCats = $this->categoryApi->getSubCategories($newParentId, true, true, true, true, true);
        $newParent = $newParentCats[0];

        if (!$newParent || !$cats) {
            return false;
        }

        $currentPaths = [];
        foreach ($newParentCats as $p) {
            $currentPaths[] = $p['path_relative'];
        }

        // need to make sure that after copying categories will have unique paths
        foreach ($cats as $k => $cat) {
            if ($includeRoot) {
                // root node is included - just check path uniqueness for root
                // subnodes will inherit it's name in paths
                $catBasePath = $newParent['path_relative'] . '/';
                if ($k === 0 && in_array($catBasePath . $cats[0]['name'], $currentPaths)) {
                    // path is not unique - add arbitrary " Copy" suffix to category name
                    $cats[0]['name'] .= ' ' . $this->translator->__('Copy');
                    if (in_array($catBasePath . $cats[0]['name'], $currentPaths)) {
                        // if there is already such name
                        // find first free name by adding a number at the end
                        $i = 1;
                        $name = $cats[0]['name'];
                        while (in_array($catBasePath . $name, $currentPaths)) {
                            $name = $cats[0]['name'] . ' ' . $i++;
                        }
                        $cats[0]['name'] = $name;
                    }
                }
            } elseif ($k !== 0) {
                // root node is excluded - need to check each subnode if it's path will be unique
                // follow the same routine that for the root node
                $catPath = explode('/', $cat['path_relative']);
                array_shift($catPath);
                array_pop($catPath);
                $catBasePath = $newParent['path_relative'] . '/' . implode('/', $catPath);
                if (in_array($catBasePath . $cats[$k]['name'], $currentPaths)) {
                    $cats[$k]['name'] .= ' ' . $this->translator->__('Copy');
                    if (in_array($catBasePath . $cats[$k]['name'], $currentPaths)) {
                        // if there is already such name
                        // find first free name by adding a number at the end
                        $i = 1;
                        $name = $cats[$k]['name'];
                        while (in_array($catBasePath . $name, $currentPaths)) {
                            $name = $cats[$k]['name'] . ' ' . $i++;
                        }
                        $cats[$k]['name'] = $name;
                    }
                }
            }
        }

        $entityName = 'ZikulaCategoriesModule:CategoryEntity';

        $oldToNewID = [];
        $oldToNewID[$cats[0]['parent']['id']] = $this->entityManager->getReference($entityName, $newParent['id']);

        // since array_shift() resets numeric array indexes, we remove the leading element like this
        if (!$includeRoot) {
            foreach ($cats as $k => $v) {
                if (isset($v['ipath']) && $v['ipath'] == $apath) {
                    unset($cats[$k]);
                }
            }
        }

        $ak = array_keys($cats);
        foreach ($ak as $v) {
            $cat = $cats[$v];

            // unset some variables
            unset($cat['parent_id']);
            unset($cat['accessible']);
            unset($cat['path_relative']);
            unset($cat['ipath_relative']);

            $oldID = $cat['id'];
            $cat['id'] = '';
            $cat['parent'] = isset($oldToNewID[$cat['parent']['id']]) ? $oldToNewID[$cat['parent']['id']] : $this->entityManager->getReference($entityName, $newParent['id']);

            $catObj = new CategoryEntity();
            $catObj->merge($cat);
            $this->entityManager->persist($catObj);
            $this->entityManager->flush();

            $oldToNewID[$oldID] = $this->entityManager->getReference($entityName, $catObj['id']);
        }

        $this->entityManager->flush();

        // rebuild iPath since now we have all new PathIDs
        $this->pathBuilder->rebuildPaths('ipath', 'id');

        // rebuild also paths since names could be changed
        $this->pathBuilder->rebuildPaths();

        return true;
    }

    /**
     * Copy subcategories by path (recursive copy).
     *
     * @param string  $apath       The path to copy from
     * @param integer $newParentId The categoryID of the new parent category
     * @param string  $pathField   The field to use for the path reference (optional) (default='ipath')
     *
     * @return boolean
     */
    public function copySubCategoriesByPath($apath, $newParentId, $pathField = 'ipath')
    {
        return $this->copyCategoriesByPath($apath, $newParentId, $pathField, false);
    }
}
