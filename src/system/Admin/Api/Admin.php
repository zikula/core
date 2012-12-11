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

class Admin_Api_Admin extends Zikula_AbstractApi
{
    /**
     * create a admin category
     * @param  string $args['name']        name of the category
     * @param  string $args['description'] description of the category
     * @return mixed  admin category ID on success, false on failure
     */
    public function create($args)
    {
        // Argument check
        if (!isset($args['name']) || !strlen($args['name']) ||
            !isset($args['description'])) {
            return LogUtil::registerArgsError();
        }

        $args['sortorder'] = ModUtil::apiFunc('Admin', 'admin', 'countitems');

        $item = new Admin_Entity_AdminCategory();
        $item->merge($args);
        $this->entityManager->persist($item);
        $this->entityManager->flush();

        // Return the id of the newly created item to the calling process
        return $item['cid'];
    }

    /**
     * update a admin category
     * @param  int    $args['cid']         the ID of the category
     * @param  string $args['name']        the new name of the category
     * @param  string $args['description'] the new description of the category
     * @return bool   true on success, false on failure
     */
    public function update($args)
    {
        // Argument check
        if (!isset($args['cid']) || !is_numeric($args['cid']) ||
            !isset($args['name']) || !strlen($args['name']) ||
            !isset($args['description'])) {
            return LogUtil::registerArgsError();
        }

        // Get the existing item
        $item = ModUtil::apiFunc('Admin', 'admin', 'get', array('cid' => $args['cid']));

        if (empty($item)) {
            return LogUtil::registerError($this->__('Sorry! No such item found.'));
        }

        // Security check (old item)
        if (!SecurityUtil::checkPermission('Admin::Category', "$item[name]::$args[cid]", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError ();
        }

        $item->merge($args);
        $this->entityManager->flush();

        // Let the calling process know that we have finished successfully
        return true;
    }

    /**
     * delete a admin category
     * @param  int  $args['cid'] ID of the category
     * @return bool true on success, false on failure
     */
    public function delete($args)
    {
        if (!isset($args['cid']) || !is_numeric($args['cid'])) {
            return LogUtil::registerArgsError();
        }

        $item = ModUtil::apiFunc('Admin', 'admin', 'get', array('cid' => $args['cid']));
        if (empty($item)) {
            return LogUtil::registerError($this->__('Sorry! No such item found.'));
        }

        // Avoid deletion of the default category
        $defaultcategory = $this->getVar('defaultcategory');
        if ($item['cid'] == $defaultcategory) {
            return LogUtil::registerError($this->__('Error! You cannot delete the default module category used in the administration panel.'));
        }

        // Avoid deletion of the start category
        $startcategory = $this->getVar('startcategory');
        if ($item['cid'] == $startcategory) {
            return LogUtil::registerError($this->__('Error! This module category is currently set as the category that is initially displayed when you visit the administration panel. You must first select a different category for initial display. Afterwards, you will be able to delete the category you have just attempted to remove.'));
        }

        // move all modules from the category to be deleted into the
        // default category.
        $entity = $this->name . '_Entity_AdminModule';
        $dql = "UPDATE $entity m SET m.cid = {$defaultcategory} WHERE m.cid = {$item['cid']}";
        $query = $this->entityManager->createQuery($dql);
        $query->getResult();

        // Now actually delete the category
        $this->entityManager->remove($item);
        $this->entityManager->flush();

        // Let the calling process know that we have finished successfully
        return true;
    }

    /**
     * get all admin categories
     * @param  int   $args['startnum'] starting record number
     * @param  int   $args['numitems'] number of items to get
     * @return mixed array of items, or false on failure
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

        $items = array();

        // Security check
        if (!SecurityUtil::checkPermission('Admin::', '::', ACCESS_READ)) {
            return $items;
        }

        $entity = $this->name . '_Entity_AdminCategory';
        $items = $this->entityManager->getRepository($entity)->findBy(array(), array('sortorder' => 'ASC'), $args['numitems'], $args['startnum']);

        return $items;
    }

    /**
     * utility function to count the number of items held by this module
     * @return int number of items held by this module
     */
    public function countitems()
    {
        $entity = $this->name . '_Entity_AdminCategory';
        $dql = "SELECT count(c.cid) FROM $entity c";
        $query = $this->entityManager->createQuery($dql);
        $numitems = $query->getSingleScalarResult();

        return (int)$numitems;
    }

    /**
     * get a specific category
     * @param  int   $args['cid'] id of example item to get
     * @return mixed item array, or false on failure
     */
    public function get($args)
    {
        // Argument check
        if (!isset($args['cid'])) {
            return LogUtil::registerArgsError();
        }

        // retrieve the category object
        $entity = $this->name . '_Entity_AdminCategory';
        $category = $this->entityManager->getRepository($entity)->findOneBy(array('cid' => (int)$args['cid']));

        if (!$category) {
            return array();
        }

        // Return the item array
        return $category;
    }

    /**
     * add a module to a category
     * @param  string $args['module']   name of the module
     * @param  int    $args['category'] number of the category
     * @return mixed  admin category ID on success, false on failure
     */
    public function addmodtocategory($args)
    {
        if (!isset($args['module']) ||
            !isset($args['category'])) {
            return LogUtil::registerArgsError();
        }

        // this function is called durung the init process so we have to check in installing
        // is set as alternative to the correct permission check
        if (!System::isInstalling() && !SecurityUtil::checkPermission('Admin::Category', "::", ACCESS_ADD)) {
            return LogUtil::registerPermissionError ();
        }

        $entity = $this->name . '_Entity_AdminModule';

        // get module id
        $mid = (int)ModUtil::getIdFromName($args['module']);

        $item = $this->entityManager->getRepository($entity)->findOneBy(array('mid' => $mid));
        if (!$item) {
            $item = new $entity;
        }

        $values = array();
        $values['cid'] = (int)$args['category'];
        $values['mid'] = $mid;
        $values['sortorder'] = ModUtil::apiFunc('Admin', 'admin', 'countModsInCat', array('cid' => $args['category']));

        $item->merge($values);
        $this->entityManager->persist($item);
        $this->entityManager->flush();

        // Return success
        return true;
    }

    /**
     * Get the category a module belongs to
     * @param  int   $args['mid'] id of the module
     * @return mixed category id, or false on failure
     */
    public function getmodcategory($args)
    {
        // create a static result set to prevent multiple sql queries
        static $catitems = array();

        // Argument check
        if (!isset($args['mid'])) {
            return LogUtil::registerArgsError();
        }

        // check if we've already worked this query out
        if (isset($catitems[$args['mid']])) {
            return $catitems[$args['mid']];
        }

        $entity = $this->name . '_Entity_AdminModule';

        // retrieve the admin module object array
        $associations = $this->entityManager->getRepository($entity)->findAll();
        if (!$associations) {
            return false;
        }

        foreach ($associations as $association) {
            $catitems[$association['mid']] = $association['cid'];
        }

        // Return the category id
        if (isset($catitems[$args['mid']])) {
            return $catitems[$args['mid']];
        }

        return false;
    }

    /**
     * Get the sortorder of a module
     * @param  int   $args['mid'] id of the module
     * @return mixed category id, or false on failure
     */
    public function getSortOrder($args)
    {
        // Argument check
        if (!isset($args['mid'])) {
            return LogUtil::registerArgsError();
        }

        static $associations = array();

        if (empty($associations)) {
            $associations = $this->entityManager->getRepository($this->name . '_Entity_AdminModule')->findAll();
        }

        $sortorder = -1;
        foreach ($associations as $association) {
            if ($association['mid'] == (int)$args['mid']) {
                $sortorder = $association['sortorder'];
                break;
            }
        }

        if ($sortorder >= 0) {
            return $sortorder;
        } else {
            return false;
        }
    }

    /**
     * Get the category a module belongs to
     * @param  int   $args['mid'] id of the module
     * @return mixed array of styles if successful, or false on failure
     */
    public function getmodstyles($args)
    {
        // check our input and get the module information
        if (!isset($args['modname']) ||
            !is_string($args['modname']) ||
            !is_array($modinfo = ModUtil::getInfoFromName($args['modname']))) {
            return LogUtil::registerArgsError();
        }

        if (!isset($args['exclude']) || !is_array($args['exclude'])) {
            $args['exclude'] = array();
        }

        // create an empty result set
        $styles = array();

        $osmoddir = DataUtil::formatForOS($modinfo['directory']);
        $base = ($modinfo['type'] == ModUtil::TYPE_SYSTEM) ? 'system' : 'modules';
        if (is_dir($dir = "$base/$osmoddir/style") || is_dir($dir = "$base/$osmoddir/pnstyle")) {
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
     * get available admin panel links
     *
     * @return array array of admin links
     */
    public function getlinks()
    {
        $links = array();

        if (SecurityUtil::checkPermission('Admin::', '::', ACCESS_READ)) {
            $links[] = array('url' => ModUtil::url('Admin', 'admin', 'view'), 'text' => $this->__('Module categories list'), 'class' => 'z-icon-es-view');
        }
        if (SecurityUtil::checkPermission('Admin::', '::', ACCESS_ADD)) {
            $links[] = array('url' => ModUtil::url('Admin', 'admin', 'newcat'), 'text' => $this->__('Create new module category'), 'class' => 'z-icon-es-new');
        }
        if (SecurityUtil::checkPermission('Admin::', '::', ACCESS_ADMIN)) {
            $links[] = array('url' => ModUtil::url('Admin', 'admin', 'help'), 'text' => $this->__('Help'), 'class' => 'z-icon-es-help');
            $links[] = array('url' => ModUtil::url('Admin', 'admin', 'modifyconfig'), 'text' => $this->__('Settings'), 'class' => 'z-icon-es-config');
        }

        return $links;
    }

    public function countModsInCat($args)
    {
        if (!isset($args['cid'])) {
            return LogUtil::registerArgsError();
        }

        $entity = $this->name . '_Entity_AdminModule';
        $dql = "SELECT count(m.amid) FROM $entity m WHERE m.cid = {$args['cid']}";
        $query = $this->entityManager->createQuery($dql);
        $count = $query->getSingleScalarResult();

        return (int)$count;
    }
}
