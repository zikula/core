<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2002, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Admin
 */

/**
 * the main administration function
 * This function is the default function, and is called whenever the
 * module is initiated without defining arguments.  As such it can
 * be used for a number of things, but most commonly it either just
 * shows the module menu and returns or calls whatever the module
 * designer feels should be the default function (often this is the
 * view() function)
 * @author Mark West
 * @return string HTML string
 */
function Admin_admin_main()
{
    // Security check will be done in view()
    return Admin_admin_view();
}

/**
 * Add a new admin category
 * This is a standard function that is called whenever an administrator
 * wishes to create a new module item
 * @author Mark West
 * @return string HTML string
 */
function Admin_admin_new()
{
    if (!SecurityUtil::checkPermission('Admin::Item', '::', ACCESS_ADD)) {
        return LogUtil::registerPermissionError();
    }

    $pnRender = Renderer::getInstance('Admin', false);

    // Return the output that has been generated by this function
    return $pnRender->fetch('admin_admin_new.htm');
}

/**
 * This is a standard function that is called with the results of the
 * form supplied by admin_admin_new() to create a new category
 * @author Mark West
 * @see Admin_admin_new()
 * @param string $args['catname'] the name of the category to be created
 * @param string $args['description'] the description of the category to be created
 * @return mixed category id if create successful, false otherwise
 */
function Admin_admin_create($args)
{
    $category = FormUtil::getPassedValue('category', isset($args['category']) ? $args['category'] : null, 'POST');

    if (!SecurityUtil::confirmAuthKey()) {
        return LogUtil::registerAuthidError(pnModURL('Admin', 'admin', 'view'));
    }

    $cid = pnModAPIFunc('Admin', 'admin', 'create',
                        array('catname' => $category['catname'],
                              'description' => $category['description']));

    if ($cid != false) {
        // Success
        LogUtil::registerStatus(__('Done! Created new category.'));
    }

    return pnRedirect(pnModURL('Admin', 'admin', 'view'));
}

/**
 * Modify a category
 * This is a standard function that is called whenever an administrator
 * wishes to modify an admin category
 * @author Mark West
 * @param int $args['cid'] category id
 * @param int $args['objectid'] generic object id maps to cid if present
 * @return string HTML string
 */
function Admin_admin_modify($args)
{
    $cid = FormUtil::getPassedValue('cid', isset($args['cid']) ? $args['cid'] : null, 'GET');
    $objectid = FormUtil::getPassedValue('objectid', isset($args['objectid']) ? $args['objectid'] : null, 'GET');

    if (!empty($objectid)) {
        $cid = $objectid;
    }

    $pnRender = Renderer::getInstance('Admin', false);

    $category = pnModAPIFunc('Admin', 'admin', 'get', array('cid' => $cid));

    if ($category == false) {
        return LogUtil::registerError(__('Error! No such category found.'), 404);
    }

    if (!SecurityUtil::checkPermission('Admin::Category', "$category[catname]::$cid", ACCESS_EDIT)) {
        return LogUtil::registerPermissionError();
    }

    $pnRender->assign('category', $category);
    return $pnRender->fetch('admin_admin_modify.htm');
}

/**
 * This is a standard function that is called with the results of the
 * form supplied by template_admin_modify() to update a current item
 * @author Mark West
 * @see Admin_admin_modify()
 * @param int $args['cid'] the id of the item to be updated
 * @param int $args['objectid'] generic object id maps to cid if present
 * @param string $args['catname'] the name of the category to be updated
 * @param string $args['description'] the description of the item to be updated
 * @return bool true if update successful, false otherwise
 */
function Admin_admin_update($args)
{
    $category = FormUtil::getPassedValue('category', isset($args['category']) ? $args['category'] : null, 'POST');
    if (!empty($category['objectid'])) {
        $category['cid'] = $category['objectid'];
    }

    if (!SecurityUtil::confirmAuthKey()) {
        return LogUtil::registerAuthidError(pnModURL('Admin', 'admin', 'view'));
    }

    if (pnModAPIFunc('Admin', 'admin', 'update',
                     array('cid' => $category['cid'],
                           'catname' => $category['catname'],
                           'description' => $category['description']))) {
        // Success
        LogUtil::registerStatus(__('Done! Saved category.'));
    }

    return pnRedirect(pnModURL('Admin', 'admin', 'view'));
}

/**
 * delete item
 * This is a standard function that is called whenever an administrator
 * wishes to delete a current module item.  Note that this function is
 * the equivalent of both of the modify() and update() functions above as
 * it both creates a form and processes its output.  This is fine for
 * simpler functions, but for more complex operations such as creation and
 * modification it is generally easier to separate them into separate
 * functions.  There is no requirement in the Zikula MDG to do one or the
 * other, so either or both can be used as seen appropriate by the module
 * developer
 * @author Mark West
 * @param int $args['cid'] the id of the category to be deleted
 * @param int $args['objectid'] generic object id maps to cid if present
 * @param bool $args['confirmation'] confirmation that this item can be deleted
 * @return mixed HTML string if confirmation is null, true if delete successful, false otherwise
 */
function Admin_admin_delete($args)
{
    $cid = FormUtil::getPassedValue('cid', isset($args['cid']) ? $args['cid'] : null, 'REQUEST');
    $objectid = FormUtil::getPassedValue('objectid', isset($args['objectid']) ? $args['objectid'] : null, 'REQUEST');
    $confirmation = FormUtil::getPassedValue('confirmation', null, 'POST');
    if (!empty($objectid)) {
        $cid = $objectid;
    }

    $category = pnModAPIFunc('Admin', 'admin', 'get', array('cid' => $cid));

    if ($category == false) {
        return LogUtil::registerError(__('Error! No such category found.'), 404);
    }

    if (!SecurityUtil::checkPermission('Admin::Category', "$category[catname]::$cid", ACCESS_DELETE)) {
        return LogUtil::registerPermissionError();
    }

    // Check for confirmation.
    if (empty($confirmation)) {
        // No confirmation yet - display a suitable form to obtain confirmation
        // of this action from the user
        $pnRender = Renderer::getInstance('Admin', false);
        $pnRender->assign('cid', $cid);
        return $pnRender->fetch('admin_admin_delete.htm');
    }

    if (!SecurityUtil::confirmAuthKey()) {
        return LogUtil::registerAuthidError(pnModURL('Admin', 'admin', 'view'));
    }

    if (pnModAPIFunc('Admin', 'admin', 'delete', array('cid' => $cid))) {
        // Success
        LogUtil::registerStatus(__('Done! Category deleted.'));
    }

    return pnRedirect(pnModURL('Admin', 'admin', 'view'));
}

/**
 * View all admin categories
 * @author Mark West
 * @param int $startnum the starting id to view from - optional
 * @return string HTML string
 */
function Admin_admin_view($args = array())
{
    if (!SecurityUtil::checkPermission('Admin::', '::', ACCESS_EDIT)) {
        return LogUtil::registerPermissionError();
    }

    $startnum = FormUtil::getPassedValue('startnum', isset($args['startnum']) ? $args['startnum'] : null, 'GET');

    $pnRender = Renderer::getInstance('Admin', false);

    $categoryArray = pnModAPIFunc('Admin', 'admin', 'getall',
                                  array('startnum' => $startnum,
                                        'numitems' => pnModGetVar('Admin', 'itemsperpage')));

    $categories = array();
    foreach ($categoryArray as $category)
    {
        if (SecurityUtil::checkPermission('Admin::', "$category[catname]::$category[cid]", ACCESS_READ)) {
            // Options for the item.
            $options = array();

            if (SecurityUtil::checkPermission('Admin::', "$category[catname]::$category[cid]", ACCESS_EDIT)) {
                $options[] = array('url' => pnModURL('Admin', 'admin', 'modify', array('cid' => $category['cid'])),
                                   'image' => 'xedit.gif',
                                   'title' => __('Edit'));

                if (SecurityUtil::checkPermission('Admin::', "$category[catname]::$category[cid]", ACCESS_DELETE)) {
                    $options[] = array('url' => pnModURL('Admin', 'admin', 'delete', array('cid' => $category['cid'])),
                                       'image' => '14_layer_deletelayer.gif',
                                       'title' => __('Delete'));
                }
            }
            $category['options'] = $options;
            $categories[] = $category;
        }
    }
    $pnRender->assign('categories', $categories);

    $pnRender->assign('pager', array('numitems' => pnModAPIFunc('Admin', 'admin', 'countitems'),
                                     'itemsperpage' => pnModGetVar('Admin', 'itemsperpage')));

    // Return the output that has been generated by this function
    return $pnRender->fetch('admin_admin_view.htm');
}

/**
 * Display main admin panel for a category
 * @author Mark West
 * @param int $args['acid'] the id of the category to be displayed
 * @return string HTML string
 */
function Admin_admin_adminpanel($args)
{
    if (!SecurityUtil::checkPermission('::', '::', ACCESS_EDIT)) {
        // suppress admin display - return to index.
        return pnRedirect(pnGetHomepageURL());
    }

    // Create output object
    $pnRender = Renderer::getInstance('Admin', false);

    if (!pnModGetVar('Admin', 'ignoreinstallercheck') && pnConfigGetVar('development') == 0) {
        // check if the Zikula Recovery Console exists
        $zrcexists = file_exists('zrc.php');
        // check if upgrade scripts exist
        if ($zrcexists == true) {
            $pnRender->assign('zrcexists', $zrcexists);
            $pnRender->assign('adminpanellink', pnModURL('Admin','admin', 'adminpanel'));
            return $pnRender->fetch('admin_admin_warning.htm');
        }
    }

    // Now prepare the display of the admin panel by getting the relevant info.

    // Get parameters from whatever input we need.
    $acid = FormUtil::getPassedValue('acid', (isset($args['acid']) ? $args['acid'] : null), 'GET');

    // cid isn't set, so we check the last session var lastcid to see where the admin has been before.
    if (empty($acid)) {
        $acid = SessionUtil::getVar('lastacid');
        if (empty($acid)) {
            // cid is still not set, go to the default category
            $acid = pnModGetVar('Admin', 'startcategory');
        }
    }

    // now we know where we are or where the admin wants us to go to, lets store it in a
    // session var for later use
    SessionUtil::setVar('lastacid', $acid);

    // Add category menu to output
    $pnRender->assign('menu', Admin_admin_categorymenu(array('acid' => $acid)));

    // Admin_admin_categorymenu may have changed the acid. In this case it has been
    // stored to lastacid so we need to read it again now
    $acid = SessionUtil::getVar('lastacid');

    // Handle the case where the current/default category does not contain any accessible items
    // (the current user may just have admin access to a single module)
    if (empty($acid)) {
        $acid = pnModGetVar('Admin', 'startcategory');
    }

    // Check to see if we have access to the requested category.
    if (!SecurityUtil::checkPermission("Admin::", "::$acid", ACCESS_ADMIN)) {
        $acid = -1;
    }

    // Get Details on the selected category
    if ($acid > 0) {
        $category = pnModAPIFunc('Admin', 'admin', 'get', array('cid' => $acid));
    } else {
        $category = null;
    }
    if (!$category) {
        // get the default category
        $acid = pnModGetVar('Admin', 'startcategory');

        // Check to see if we have access to the requested category.
        if (!SecurityUtil::checkPermission("Admin::", "::$acid", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError(pnGetHomepageURL());
        }

        $category = pnModAPIFunc('Admin', 'admin', 'get', array('cid' => $acid));
    }

    // assign the category
    $pnRender->assign('category', $category);

    // assign all module vars
    $pnRender->assign('modvars', pnModGetVar('Admin'));

    $displayNameType = pnModGetVar('Admin', 'displaynametype', 1);

    // get admin capable modules
    $adminmodules = pnModGetAdminMods();
    $adminlinks = array();
    foreach ($adminmodules as $adminmodule) {
        if (SecurityUtil::checkPermission("{$adminmodule['name']}::", 'ANY', ACCESS_EDIT)) {
            $catid = pnModAPIFunc('Admin', 'admin', 'getmodcategory',
                                  array('mid' => pnModGetIDFromName($adminmodule['name'])));

            if (($catid == $acid) || (($catid == false) && ($acid == pnModGetVar('Admin', 'defaultcategory')))) {
                $modinfo = pnModGetInfo(pnModGetIDFromName($adminmodule['name']));
                if ($modinfo['type'] == 2 || $modinfo['type'] == 3) {
                    $menutexturl = pnModURL($modinfo['name'], 'admin');
                    $modpath = ($modinfo['type'] == 3) ? 'system' : 'modules';
                } else {
                    $menutexturl = 'admin.php?module=' . $modinfo['name'];
                    $modpath = 'modules';
                }

                if ($displayNameType == 1) {
                    $menutext = $modinfo['displayname'];
                } elseif ($displayNameType == 2) {
                    $menutext = $modinfo['name'];
                } elseif ($displayNameType == 3) {
                    $menutext = $modinfo['displayname'] . ' (' . $modinfo['name'] . ')';
                }
                $menutexttitle = $modinfo['description'];

                $osmoddir = DataUtil::formatForOS($modinfo['directory']);
                $adminicons = array($modpath . '/' . $osmoddir . '/pnimages/admin.gif',
                                    $modpath . '/' . $osmoddir . '/pnimages/admin.jpg',
                                    $modpath . '/' . $osmoddir . '/pnimages/admin.jpeg',
                                    $modpath . '/' . $osmoddir . '/pnimages/admin.png',
                                    $modpath . '/' . $osmoddir . '/images/admin.gif',
                                    'system/Admin/pnimages/default.gif');

                foreach ($adminicons as $adminicon) {
                    if (is_readable($adminicon)) {
                        break;
                    }
                }

                $adminlinks[] = array('menutexturl' => $menutexturl,
                                      'menutext' => $menutext,
                                      'menutexttitle' => $menutexttitle,
                                      'modname' => $modinfo['name'],
                                      'adminicon' => $adminicon);
            }
        }
    }
    $pnRender->assign('adminlinks', $adminlinks);

    // work out what stylesheet is being used to render to the admin panel
    $css = pnModGetVar('Admin', 'modulestylesheet');
    $cssfile = explode('.', $css);

    // Return the output that has been generated by this function
    if ($pnRender->template_exists('admin_admin_adminpanel_'.$cssfile[0].'.htm')) {
        return $pnRender->fetch('admin_admin_adminpanel_'.$cssfile[0].'.htm');
    } else {
        return $pnRender->fetch('admin_admin_adminpanel.htm');
    }
}

/**
 * This is a standard function to modify the configuration parameters of the
 * module
 * @author Mark West
 * @return string HTML string
 */
function Admin_admin_modifyconfig()
{
    if (!SecurityUtil::checkPermission('Admin::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    // Create output object
    $pnRender = Renderer::getInstance('Admin', false);

    // get admin capable mods
    $adminmodules = pnModGetAdminMods();

    // Get all categories
    $categories = pnModAPIFunc('Admin', 'admin', 'getall');
    $pnRender->assign('categories', $categories);

    // assign all the module vars
    $pnRender->assign('modvars', pnModGetVar('Admin'));

    $modulecategories = array();
    foreach ($adminmodules as $adminmodule)
    {
        // Get the category assigned to this module
        $category = pnModAPIFunc('Admin', 'admin', 'getmodcategory',
                                 array('mid' => pnModGetIDFromName($adminmodule['name'])));

        if ($category === false) {
            // it's not set, so we use the default category
            $category = pnModGetVar('Admin', 'defaultcategory');
        }
        // output module category selection
        $modulecategories[] = array('displayname' => $adminmodule['displayname'],
                                    'name' => $adminmodule['name'],
                                    'category' => $category);
    }
    $pnRender->assign('modulecategories', $modulecategories);

    // Return the output that has been generated by this function
    return $pnRender->fetch('admin_admin_modifyconfig.htm');
}

/**
 * This is a standard function to update the configuration parameters of the
 * module given the information passed back by the modification form
 * @author Mark West
 * @see Admin_admin_modifyconfig()
 * @param int $modulesperrow the number of modules to display per row in the admin panel
 * @param int $admingraphic switch for display of admin icons
 * @param int $modulename,... the id of the category to set for each module
 * @return string HTML string
 */
function Admin_admin_updateconfig()
{
    if (!SecurityUtil::checkPermission('Admin::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    if (!SecurityUtil::confirmAuthKey()) {
        return LogUtil::registerAuthidError(pnModURL('Admin', 'admin', 'view'));
    }

    // get module vars
    $modvars = FormUtil::getPassedValue('modvars', null, 'POST');

    // check module vars
    $modvars['modulesperrow'] = isset($modvars['modulesperrow']) ? $modvars['modulesperrow'] : 5;
    if (!is_numeric($modvars['modulesperrow'])) {
        unset($modvars['modulesperrow']);
        LogUtil::registerError(__("Error! You must enter a number for the 'Modules per row' setting."));
    }
    $modvars['ignoreinstallercheck'] = isset($modvars['ignoreinstallercheck']) ? $modvars['ignoreinstallercheck'] : false;
    $modvars['itemsperpage'] = isset($modvars['itemsperpage']) ? $modvars['itemsperpage'] : 5;
    if (!is_numeric($modvars['itemsperpage'])) {
        unset($modvars['itemsperpage']);
        LogUtil::registerError(__("Error! You must enter a number for the 'Modules per page' setting."));
    }
    $modvars['modulestylesheet'] = isset($modvars['modulestylesheet']) ? $modvars['modulestylesheet'] : 'navtabs.css';
    $modvars['admingraphic'] = isset($modvars['admingraphic']) ? $modvars['admingraphic'] : 0;
    $modvars['moduledescription'] = isset($modvars['moduledescription']) ? $modvars['moduledescription'] : 0;
    $modvars['displaynametype'] = isset($modvars['displaynametype']) ? $modvars['displaynametype'] : 1;
    $modvars['startcategory'] = isset($modvars['startcategory']) ? $modvars['startcategory'] : 1;
    $modvars['defaultcategory'] = isset($modvars['defaultcategory']) ? $modvars['defaultcategory'] : 1;
    $modvars['admintheme'] = isset($modvars['admintheme']) ? $modvars['admintheme'] : null;

    // save module vars
    pnModSetVars('Admin', $modvars);

    // get admin modules
    $adminmodules = pnModGetAdminMods();
    $adminmods = FormUtil::getPassedValue('adminmods', null, 'POST');

    foreach ($adminmodules as $adminmodule) {
        $category = $adminmods[$adminmodule['name']];

        if ($category) {
            // Add the module to the category
            $result = pnModAPIFunc('Admin', 'admin', 'addmodtocategory',
                                   array('module' => $adminmodule['name'],
                                         'category' => $category));
            if ($result == false) {
                LogUtil::registerError(__('Error! Could not add module to module category.'));
                return pnRedirect(pnModURL('Admin', 'admin', 'view'));
            }
        }
    }

    // Let any other modules know that the modules configuration has been updated
    pnModCallHooks('module','updateconfig','Admin', array('module' => 'Admin'));

    // the module configuration has been updated successfuly
    LogUtil::registerStatus(__('Done! Saved module configuration.'));

    // This function generated no output, and so now it is complete we redirect
    // the user to an appropriate page for them to carry on their work
    return pnRedirect(pnModURL('Admin', 'admin', 'main'));
}

/**
 * Main category menu
 * @author Mark West
 * @return string HTML string
 */
function Admin_admin_categorymenu($args)
{
    // get the current category
    $acid = FormUtil::getPassedValue('acid', isset($args['acid']) ? $args['acid'] : SessionUtil::getVar('lastacid'), 'GET');
    if (empty($acid)) {
        // cid is still not set, go to the default category
        $acid = pnModGetVar('Admin', 'startcategory');
    }

    // Get all categories
    $categories = pnModAPIFunc('Admin', 'admin', 'getall');

    // get admin capable modules
    $adminmodules = pnModGetAdminMods();
    $adminlinks = array();

    foreach ($adminmodules as $adminmodule) {
        if (SecurityUtil::checkPermission("$adminmodule[name]::", '::', ACCESS_EDIT)) {
            $catid = pnModAPIFunc('Admin', 'admin', 'getmodcategory', array('mid' => $adminmodule['id']));
            if ($adminmodule['type'] == 2 || $adminmodule['type'] == 3) {
                $menutexturl = pnModURL($adminmodule['name'], 'admin');
                $menutext = $adminmodule['displayname'];
                $menutexttitle = $adminmodule['description'];
            } else {
                $menutexturl = 'admin.php?module=' . $adminmodule['name'];
                $menutext = $adminmodule['displayname'];
                $menutexttitle =  $adminmodule['description'];
            }
            $adminlinks[$catid][] = array('menutexturl' => $menutexturl,
                                          'menutext' => $menutext,
                                          'menutexttitle' => $menutexttitle,
                                          'modname' => $adminmodule['name']);
        }
    }

    $menuoptions = array();
    $possible_cids = array();
    $permission = false;

    if (isset($categories) && is_array($categories)) {
        foreach($categories as $category) {
            // only categories containing modules where the current user has permissions will
            // be shown, all others will be hidden
            // admin will see all categories
            if ( (isset($adminlinks[$category['cid']]) && count($adminlinks[$category['cid']]) )
               || SecurityUtil::checkPermission('.*', '.*', ACCESS_ADMIN) ) {
                $menuoption = array('url'         => pnModURL('Admin','admin','adminpanel', array('acid' => $category['cid'])),
                                    'title'       => $category['catname'],
                                    'description' => $category['description'],
                                    'cid'         => $category['cid']);
                if (isset($adminlinks[$category['cid']])) {
                    $menuoption['items'] = $adminlinks[$category['cid']];
                } else {
                    $menuoption['items'] = array();
                }
                $menuoptions[] = $menuoption;
                $possible_cids[] = $category['cid'];
                if ($acid == $category['cid']) {
                    $permission =true;
                }
            }
        }
    }

    // if permission is false we are not allowed to see this category because its
    // empty and we are not admin
    if ($permission==false) {
        // show the first category
        $acid = !empty($possible_cids) ? (int)$possible_cids[0] : null;
    }

    // store it
    SessionUtil::setVar('lastcid', $acid);

    $pnRender = Renderer::getInstance('Admin', false);

    $pnRender->assign('currentcat', $acid);
    $pnRender->assign('menuoptions', $menuoptions);

    // security analyzer and update checker warnings
    $notices = array();
    $notices['security'] = _Admin_admin_securityanalyzer();
    $notices['update'] = _Admin_admin_updatecheck();
    $pnRender->assign('notices', $notices);

    // work out what stylesheet is being used to render to the admin panel
    $css = pnModGetVar('Admin', 'modulestylesheet');
    $cssfile = explode('.', $css);

    // Return the output that has been generated by this function
    if ($pnRender->template_exists('admin_admin_categorymenu_'.$cssfile[0].'.htm')) {
        return $pnRender->fetch('admin_admin_categorymenu_'.$cssfile[0].'.htm');
    } else {
        return $pnRender->fetch('admin_admin_categorymenu.htm');
    }
}

/**
 * display the module help page
 *
 */
function Admin_admin_help()
{
    if (!SecurityUtil::checkPermission('Admin::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }
    $pnRender = Renderer::getInstance('Admin', false);
    return $pnRender->fetch('admin_admin_help.htm');
}

/**
 * Get security analyzer data
 * @author Mark West
 * @return array data
 */
function _Admin_admin_securityanalyzer()
{
    $data = array();

    // check for magic_quotes
    $data['magic_quotes_gpc'] = DataUtil::getBooleanIniValue('magic_quotes_gpc');

    // check for register_globals
    $data['register_globals'] = DataUtil::getBooleanIniValue('register_globals');

    // check for config.php beeing writable
    // cannot rely on is_writable() because it falsely reports a number of cases - drak
    $config_php = @fopen('config/config.php', 'a');
    if ($config_php === true) {
        fclose($config_php);
    }
    $data['config_php'] = (bool)$config_php;

    // check for .htaccess in temp directory
    $temp_htaccess = false;
    $tempDir = $GLOBALS['ZConfig']['System']['temp'];
    if ($tempDir) {
        // check if we have an absolute path which is possibly not within the document root
        $docRoot = pnServerGetVar('DOCUMENT_ROOT');
        if (StringUtil::left($tempDir, 1) == '/' && (strpos($tempDir, $docRoot) === false)) {
            // temp dir is outside the webroot, no .htaccess file needed
            $temp_htaccess = true;
        } else {
            if (strpos($tempDir, $docRoot) === false) {
                $ldir = dirname(__FILE__);
                $p = strpos($ldir, DIRECTORY_SEPARATOR.'system'); // we are in system/Admin
                $b = substr($ldir,0,$p);
                $filePath = $b.'/'.$tempDir.'/.htaccess';
            } else {
                $filePath = $tempDir.'/.htaccess';
            }
            $temp_htaccess = (bool) file_exists($filePath);
        }
    } else {
        // already customized, admin should know about what he's doing...
        $temp_htaccess = true;
    }
    $data['temp_htaccess'] = $temp_htaccess;

    $data['scactive']  = (bool)pnModAvailable('SecurityCenter');

    // check for outputfilter
    $data['useids'] = (bool)(pnModAvailable('SecurityCenter') && pnConfigGetVar('useids') == 1);

    return $data;
}


/**
 * Check for updates
 *
 * @author Drak
 * @return data or false
 */
function _Admin_admin_updatecheck($force=false)
{
    if (!pnConfigGetVar('updatecheck')) {
        return array('update_show' => false);
    }

    $now = time();
    $lastChecked = (int)pnConfigGetVar('updatelastchecked');
    $checkInterval = (int)pnConfigGetVar('updatefrequency') * 86400;
    $updateversion = pnConfigGetVar('updateversion');

    if ($force == false && (($now - $lastChecked) < $checkInterval)) {
        // dont get an update because TTL not expired yet
        $onlineVersion = $updateversion;
    } else {
        $s = (extension_loaded('openssl') ? 's' : '');
        $onlineVersion = trim(_Admin_admin_zcurl("http$s://update.zikula.org/cgi-bin/engine/checkcoreversion.cgi"));
        if ($onlineVersion === false) {
            return array('update_show' => false);
        }
        pnConfigSetVar('updateversion', $onlineVersion);
        pnConfigSetVar('updatelastchecked', (int)time());
    }

    // if 1 then there is a later version available
    if (version_compare($onlineVersion, PN_VERSION_NUM) == 1) {
        return array('update_show' => true,
                     'update_version' => $onlineVersion);
    } else {
        return array('update_show' => false);
    }
}


/**
 * Zikula curl
 *
 * This function is internal for the time being and may be extended to be a proper library
 * or find an alternative solution later.
 *
 * @author Drak
 *
 * @todo relocate this somewhere sensible after feature has been correctly implemented - drak
 *
 * @param string $url
 * @param ing $timeout default=5
 * @return mixed, false or string
 */
function _Admin_admin_zcurl($url, $timeout=5)
{
    $urlArray = parse_url($url);
    $data = '';
    $userAgent = 'Zikula/' . PN_VERSION_NUM;
    $ref = pnGetBaseURL();
    $port = (($urlArray['scheme'] == 'https') ? 443 : 80);
    if (ini_get('allow_url_fopen')) {
        // handle SSL connections
        $path_query = (isset($urlArray['query']) ? $urlArray['path'] . $urlArray['query'] : $urlArray['path']);
        $host = ($port==443 ? "ssl://$urlArray[host]" : $urlArray['host']);
        $fp = fsockopen($host, $port, $errno, $errstr, $timeout);
        if (!$fp) {
            return false;
        } else {
            $out = "GET $path_query? HTTP/1.1\r\n";
            $out .= "User-Agent: $userAgent\r\n";
            $out .= "Referer: $ref\r\n";
            $out .= "Host: $urlArray[host]\r\n";
            $out .= "Connection: Close\r\n\r\n";
            fwrite($fp, $out);
            while (!feof($fp)) {
                $data .= fgets($fp, 1024);
            }
            fclose($fp);
            $dataArray = explode("\r\n\r\n", $data);

            return $dataArray[1];
        }
    } elseif (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_URL, "$url?");
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($ch, CURLOPT_REFERER, $ref);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        $data = curl_exec($ch);
        if (!$data && $port=443) {
            // retry non ssl
            $url = str_replace('https://', 'http://', $url);
            curl_setopt($ch, CURLOPT_URL, "$url?");
            $data = curl_exec($ch);
        }
        //$headers = curl_getinfo($ch);
        curl_close($ch);
        return $data;
    } else {
        return false;
    }
}
