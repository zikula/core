<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\AdminModule\Api;

use DataUtil;
use ModUtil;
use ServiceUtil;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use System;
use Zikula\AdminModule\Entity\AdminCategoryEntity;
use Zikula\AdminModule\Entity\AdminModuleEntity;

/**
 * API functions used by administrative controllers
 * @deprecated remove at Core-2.0
 */
class AdminApi
{
    /**
     * create an admin category
     *
     * @param mixed[] $args {
     *      @type string $name        name of the category
     *      @type string $description description of the category
     *                       }
     *
     * @return int|bool admin category ID on success, false on failure
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received in $args
     */
    public function create($args)
    {
        // Argument check
        if (!isset($args['name']) || !strlen($args['name']) ||
            !isset($args['description'])) {
            $translator = ServiceUtil::get('translator.default');
            throw new \InvalidArgumentException($translator->__('Invalid arguments array received'));
        }

        $entityManager = ServiceUtil::get('doctrine')->getManager();
        $args['sortorder'] = $entityManager->getRepository('ZikulaAdminModule:AdminCategoryEntity')->countCategories();

        $item = new AdminCategoryEntity();
        $item->merge($args);
        $entityManager->persist($item);
        $entityManager->flush();

        // Return the id of the newly created item to the calling process
        return $item['cid'];
    }

    /**
     * update an admin category
     *
     * @param mixed[] $args {
     *      @type int    $cid         the ID of the category
     *      @type string $name        the new name of the category
     *      @type string $description the new description of the category
     *                       }
     *
     * @return bool true on success, false on failure
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received in $args
     * @throws AccessDeniedException Thrown if the user doesn't have permission to update the item
     */
    public function update($args)
    {
        // Argument check
        if (!isset($args['cid']) || !is_numeric($args['cid']) ||
            !isset($args['name']) || !strlen($args['name']) ||
            !isset($args['description'])) {
            $translator = ServiceUtil::get('translator.default');
            throw new \InvalidArgumentException($translator->__('Invalid arguments array received'));
        }

        // Get the existing item
        /** @var AdminCategoryEntity $item */
        $item = $this->getCategory(['cid' => $args['cid']]);
        if (empty($item)) {
            return false;
        }

        // Security check (old item)
        $permissionApi = ServiceUtil::get('zikula_permissions_module.api.permission');
        if (!$permissionApi->getPermission('ZikulaAdminModule::Category', "$item[name]::$args[cid]", ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $entityManager = ServiceUtil::get('doctrine')->getManager();

        $item->merge($args);
        $entityManager->flush();

        // Let the calling process know that we have finished successfully
        return true;
    }

    /**
     * delete an admin category
     *
     * @param  int[] $args {
     *      @type int $args['cid'] ID of the category
     *                      }
     *
     * @return bool true on success, false on failure
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received in $args
     * @throws \RuntimeException Thrown if the category to be deleted is the default for new modules or
     *                                  if the category to be deleted is the initial category to be displayed
     */
    public function delete($args)
    {
        $translator = ServiceUtil::get('translator.default');
        if (!isset($args['cid']) || !is_numeric($args['cid'])) {
            throw new \InvalidArgumentException($translator->__('Invalid arguments array received'));
        }

        $item = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getCategory', ['cid' => $args['cid']]);
        if (empty($item)) {
            return false;
        }

        $variableApi = ServiceUtil::get('zikula_extensions_module.api.variable');

        // Avoid deletion of the default category
        $defaultcategory = $variableApi->get('ZikulaAdminModule', 'defaultcategory');
        if ($item['cid'] == $defaultcategory) {
            throw new \RuntimeException($translator->__('Error! You cannot delete the default module category used in the administration panel.'));
        }

        // Avoid deletion of the start category
        $startcategory = $variableApi->get('ZikulaAdminModule', 'startcategory');
        if ($item['cid'] == $startcategory) {
            throw new \RuntimeException($translator->__('Error! This module category is currently set as the category that is initially displayed when you visit the administration panel. You must first select a different category for initial display. Afterwards, you will be able to delete the category you have just attempted to remove.'));
        }

        $entityManager = ServiceUtil::get('doctrine')->getManager();

        // move all modules from the category to be deleted into the
        // default category.
        $entityManager->getRepository('ZikulaAdminModule:AdminModuleEntity')->changeCategory($item['cid'], $defaultcategory);

        // Now actually delete the category
        $entityManager->remove($item);
        $entityManager->flush();

        // Let the calling process know that we have finished successfully
        return true;
    }

    /**
     * get all admin categories
     *
     * @param int[] $args {
     *      @type int $startnum starting record number
     *      @type int $numitems number of items to get
     *                     }
     *
     * @return array|bool array of items, or false on failure
     */
    public function getall($args)
    {
        // Optional arguments.
        if (!isset($args['startnum']) || !is_numeric($args['startnum'])) {
            $args['startnum'] = null;
        }
        if (!isset($args['numitems']) || !is_numeric($args['numitems'])) {
            $args['numitems'] = null;
        }

        $items = [];

        // Security check
        $permissionApi = ServiceUtil::get('zikula_permissions_module.api.permission');
        $isUpgrading = \ServiceUtil::getManager()->hasParameter('upgrading') && \ServiceUtil::getManager()->getParameter('upgrading');
        if (!$isUpgrading && !$permissionApi->hasPermission('ZikulaAdminModule::', '::', ACCESS_READ)) {
            return $items;
        }

        $entityManager = ServiceUtil::get('doctrine')->getManager();

        $items = $entityManager->getRepository('ZikulaAdminModule:AdminCategoryEntity')->findBy([], ['sortorder' => 'ASC'], $args['numitems'], $args['startnum']);

        return $items;
    }

    /**
     * utility function to count the number of items held by this module
     *
     * @return int number of items held by this module
     */
    public function countitems()
    {
        $entityManager = ServiceUtil::get('doctrine')->getManager();

        return $entityManager->getRepository('ZikulaAdminModule:AdminCategoryEntity')->countCategories();
    }

    /**
     * get a specific category
     *
     * @param int[] $args {
     *      @type int $cid id of example item to get
     *                     }
     *
     * @return AdminCategoryEntity|bool item , or false on failure
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received in $args
     */
    public function getCategory($args)
    {
        // Argument check
        if (!isset($args['cid']) || !is_numeric($args['cid'])) {
            $translator = ServiceUtil::get('translator.default');
            throw new \InvalidArgumentException($translator->__('Invalid arguments array received'));
        }

        $entityManager = ServiceUtil::get('doctrine')->getManager();

        // retrieve the category object
        $category = $entityManager->getRepository('ZikulaAdminModule:AdminCategoryEntity')->findOneBy(['cid' => (int)$args['cid']]);
        if (!$category) {
            return [];
        }

        // Return the item array
        return $category;
    }

    /**
     * add a module to a category
     *
     * @param mixed[] $args {
     *      @type  string $module   name of the module
     *      @type  int    $category number of the category
     *                       }
     *
     * @return int|bool admin category ID on success, false on failure
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received in $args
     * @throws AccessDeniedException Thrown if the user doesn't have permission to add the category
     */
    public function addmodtocategory($args)
    {
        if (!isset($args['module']) ||
            (!isset($args['category']) || !is_numeric($args['category']))) {
            $translator = ServiceUtil::get('translator.default');
            throw new \InvalidArgumentException($translator->__('Invalid arguments array received'));
        }

        // this function is called during the init process so we have to check in installing
        // is set as alternative to the correct permission check
        $permissionApi = ServiceUtil::get('zikula_permissions_module.api.permission');
        if (ServiceUtil::getManager()->getParameter('installed') && !$permissionApi->hasPermission('ZikulaAdminModule::Category', "::", ACCESS_ADD)) {
            throw new AccessDeniedException();
        }

        // get module id
        $mid = (int)ModUtil::getIdFromName($args['module']);

        $entityManager = ServiceUtil::get('doctrine')->getManager();

        $item = $entityManager->getRepository('ZikulaAdminModule:AdminModuleEntity')->findOneBy(['mid' => $mid]);
        if (!$item) {
            $item = new AdminModuleEntity();
        }

        $item->setMid($mid);
        $item->setCid((int)$args['category']);
        $item->setSortorder(ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'countModsInCat', ['cid' => $args['category']]));

        $entityManager->persist($item);
        $entityManager->flush();

        // Return success
        return true;
    }

    /**
     * Get the category a module belongs to
     *
     * @param int[] $args {
     *      @type int $mid id of the module
     *                     }
     *
     * @return int|false category id, or false on failure
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received in $args
     */
    public function getmodcategory($args)
    {
        // create a static result set to prevent multiple sql queries
        static $catitems = [];

        // Argument check
        if (!isset($args['mid']) || !is_numeric($args['mid'])) {
            $translator = ServiceUtil::get('translator.default');
            throw new \InvalidArgumentException($translator->__('Invalid arguments array received'));
        }

        $entityManager = ServiceUtil::get('doctrine')->getManager();

        // retrieve the admin category for this module
        $category = $entityManager->getRepository('ZikulaAdminModule:AdminCategoryEntity')->getModuleCategory($args['mid']);

        return null !== $category ? $category['cid'] : false;
    }

    /**
     * Get the sort order of a module
     *
     * @param int[] $args {
     *      @type int $mid id of the module
     *                     }
     *
     * @return int|bool category id, or false on failure
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received in $args
     */
    public function getSortOrder($args)
    {
        // Argument check
        if (!isset($args['mid']) || !is_numeric($args['mid'])) {
            $translator = ServiceUtil::get('translator.default');
            throw new \InvalidArgumentException($translator->__('Invalid arguments array received'));
        }

        static $associations = [];

        if (empty($associations)) {
            $entityManager = ServiceUtil::get('doctrine')->getManager();
            $associations = $entityManager->getRepository('ZikulaAdminModule:AdminModuleEntity')->findAll();
        }

        $sortOrder = -1;
        $moduleId = (int)$args['mid'];
        foreach ($associations as $association) {
            if ($association['mid'] != $moduleId) {
                continue;
            }

            $sortOrder = $association['sortorder'];
            break;
        }

        if ($sortOrder >= 0) {
            return $sortOrder;
        }

        return false;
    }

    /**
     * Get the category a module belongs to
     *
     * @param int[] $args {
     *      @type int $mid id of the module
     *                     }
     *
     * @return array|bool array of styles if successful, or false on failure
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received in $args
     */
    public function getmodstyles($args)
    {
        // check our input and get the module information
        if (!isset($args['modname']) ||
            !is_string($args['modname']) ||
            !is_array($modinfo = ModUtil::getInfoFromName($args['modname']))) {
            $translator = ServiceUtil::get('translator.default');
            throw new \InvalidArgumentException($translator->__('Invalid arguments array received'));
        }

        if (!isset($args['exclude']) || !is_array($args['exclude'])) {
            $args['exclude'] = [];
        }

        // create an empty result set
        $styles = [];

        $osmoddir = DataUtil::formatForOS($modinfo['directory']);
        $base = (ModUtil::TYPE_SYSTEM == $modinfo['type']) ? 'system' : 'modules';

        $mpath = ModUtil::getModuleRelativePath($modinfo['name']);
        if ($mpath) {
            $path = $mpath.'/Resources/public/css';
        }

        if ((isset($path) && is_dir($dir = $path))
            || is_dir($dir = "$base/$osmoddir/style")
            || is_dir($dir = "$base/$osmoddir/pnstyle")) {
            $handle = opendir($dir);
            while (false !== ($file = readdir($handle))) {
                if (stristr($file, '.css') && !in_array($file, $args['exclude'])) {
                    $styles[] = $file;
                }
            }
        }

        // return our results
        return $styles;
    }

    /**
     * count modules in a given category
     *
     * @param int[] $args {
     *      @type int $cid id of the category
     *                     }
     *
     * @return int   number of modules
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received in $args
     */
    public function countModsInCat($args)
    {
        if (!isset($args['cid']) || !is_numeric($args['cid'])) {
            $translator = ServiceUtil::get('translator.default');
            throw new \InvalidArgumentException($translator->__('Invalid arguments array received'));
        }

        $entityManager = ServiceUtil::get('doctrine')->getManager();

        return $entityManager->getRepository('ZikulaAdminModule:AdminModuleEntity')->countModulesByCategory($args['cid']);
    }
}
