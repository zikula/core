<?php
/**
 * Change the category a module belongs to by ajax.
 * 
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
        AjaxUtil::output($output);
        return;
    }
    $module = $module['name'];
    $result = pnModAPIFunc('Admin', 'admin', 'addmodtocategory', array('category' => $newParentCat,
        'module' => $module));
    $output['alerttext'] = '';
    $output['response'] = ($result) ? "1" : "0";
    AjaxUtil::output($output);
}

/**
 * Add a new admin category by ajax.
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