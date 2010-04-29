<?php
/**
 * Copyright 2009 Zikula Foundation - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option, any later version).
 * @package Zikula
 * @link http://www.zikula.org
 * @version $Id$
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */ 

/**
 * Change the category a module belongs to by ajax.
 *
 * @return AjaxUtil::output Output to the calling ajax request is returned. 
 *                          alerttext is a string empty if no problems.
 *                          response is a string -1 on failure moduleid on sucess.
 */
function Admin_Ajax_changeModuleCategory() {
    if (!SecurityUtil::checkPermission('Admin::', '::', ACCESS_ADMIN)) {
        return AjaxUtil::output('You do not have permission to do this.');
    }
    $moduleID = FormUtil::getPassedValue("modid");
    $newParentCat = FormUtil::getPassedValue("cat");
    $module = pnModGetInfo($moduleID);
    if (!$module) {
        //deal with couldnt get category id
        $output["alerttext"] = "Could not get module name for id:$moduleID";
        return AjaxUtil::output($output);
    }
    $module = $module['name'];
    $result = pnModAPIFunc('Admin', 'admin', 'addmodtocategory', array('category' => $newParentCat,
        'module' => $module));
    $output['alerttext'] = '';
    $output['response'] = ($result) ? $moduleID : "-1";
    return AjaxUtil::output($output);
}

/**
 * Add a new admin category by ajax.
 * 
 * @return AjaxUtil::output Output to the calling ajax request is returned. 
 *                          alerttext is a string empty if no problems.
 *                          response is a string 0 on failure new cid on sucess. 
 *                          url is a formatted url to the new category on success.
 */
function Admin_Ajax_addCategory() {
    if (!SecurityUtil::checkPermission('Admin::', '::', ACCESS_ADD)) {
        return AjaxUtil::output('You do not have permission to do this.');
    }
    $catName = trim(FormUtil::getPassedValue('catname'));
    $cats = pnModAPIFunc('Admin', 'admin', 'getall');
    foreach ($cats as $cat) {
        if (in_array($catName, $cat)) {
            $output['alerttext'] = 'A category by this name already exists.';
            return AjaxUtil::output($output);
        }
    }
    $result = pnModAPIFunc('Admin', 'admin', 'create', array('catname' => $catName,
        'description' => ''));
    $output['alerttext'] = '';
    $output['response'] = (!$result) ? "0" : $result;
    $url = pnModURL('adminpanel', 'admin', 'adminpanel', array('acid' => $result));
    $output['url'] = $url;
    AjaxUtil::output($output);
}

/**
 * Delete an admin category by ajax.
 * 
 * @return AjaxUtil::output Output to the calling ajax request is returned. 
 *                          alerttext is a string empty if no problems.
 *                          response is a string -1 on failure deleted cid on sucess.
 */
function Admin_Ajax_deleteCategory() {
    $cid = trim(FormUtil::getPassedValue('cid'));
    $category = pnModAPIFunc('Admin', 'admin', 'get', array('cid' => $cid));
    if ($category == false) {
        $output['alerttext'] = 'Could not find category:'.$cid;
        $output['response'] = '-1';
        return AjaxUtil::output($output);
    }

    if (!SecurityUtil::checkPermission('Admin::Category', "$category[catname]::$cid", ACCESS_DELETE)) {
        $output['alerttext'] = 'You do not have permission to delete category:'.$cid;
        $output['response'] = '-1';
        return AjaxUtil::output($output);
    }
    if (pnModAPIFunc('Admin', 'admin', 'delete', array('cid' => $cid))) {
        // Success
        $output['alerttext'] = '';
        $output['response'] = $cid;
        return AjaxUtil::output($output);
    }
}

function Admin_Ajax_editCategory() {
    $cid = trim(FormUtil::getPassedValue('cid'));
    $cat = trim(FormUtil::getPassedValue('catname'));
    
    $category = pnModAPIFunc('Admin', 'admin', 'get', array('cid' => $cid));
    if ($category == false) {
        //header("HTTP/1.0 400 Could not find category:".$cid);
        echo "Error";
        exit;
    }

    if (!SecurityUtil::checkPermission('Admin::Category', "$category[catname]::$cid", ACCESS_EDIT)) {
        //header("HTTP/1.0 400 You do not have permission to delete category:".$cid);
        echo "Error";
        exit;
    }
    if (pnModAPIFunc('Admin', 'admin', 'update', array('cid' => $cid, 'catname' => $cat, 'description' => $category['description']))) {
    	echo $cat;
    	exit;
    }
    echo "Error";
    exit;
}