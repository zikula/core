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
class Admin_Controller_Admin extends Zikula_AbstractController
{
    /**
     * Post initialise.
     *
     * @return void
     */
    protected function postInitialize()
    {
        // In this controller we do not want caching.
        $this->view->setCaching(Zikula_View::CACHE_DISABLED);
    }

    /**
     * the main administration function
     * This function is the default function, and is called whenever the
     * module is initiated without defining arguments.  As such it can
     * be used for a number of things, but most commonly it either just
     * shows the module menu and returns or calls whatever the module
     * designer feels should be the default function (often this is the
     * view() function)
     * @return string HTML string
     */
    public function mainAction()
    {
        // Security check will be done in view()
        $this->redirect(ModUtil::url('Admin', 'admin', 'view'));
    }

    /**
     * View all admin categories
     *
     * @param  int    $startnum the starting id to view from - optional
     * @return string HTML string
     */
    public function viewAction($args = array())
    {
        if (!SecurityUtil::checkPermission('Admin::', '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        $startnum = (int)FormUtil::getPassedValue('startnum', isset($args['startnum']) ? $args['startnum'] : 0, 'GET');
        $itemsperpage = $this->getVar('itemsperpage');

        $categories = array();
        $items = ModUtil::apiFunc('Admin', 'admin', 'getall',
                                          array('startnum' => $startnum,
                                                'numitems' => $itemsperpage));
        foreach ($items as $item) {
            if (SecurityUtil::checkPermission('Admin::', "$item[name]::$item[cid]", ACCESS_READ)) {
                $categories[] = $item;
            }
        }
        $this->view->assign('categories', $categories);

        $numitems = ModUtil::apiFunc('Admin', 'admin', 'countitems');
        $this->view->assign('pager', array('numitems' => $numitems,
                                           'itemsperpage' => $itemsperpage));

        // Return the output that has been generated by this function
        return $this->view->fetch('admin/view.tpl');
    }

    /**
     * Add a new admin category
     * This is a standard function that is called whenever an administrator
     * wishes to create a new module item
     * @return string HTML string
     */
    public function newcatAction()
    {
        if (!SecurityUtil::checkPermission('Admin::Item', '::', ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }

        // Return the output that has been generated by this function
        return $this->view->fetch('admin/newcat.tpl');
    }

    /**
     * This is a standard function that is called with the results of the
     * form supplied by admin_admin_new() to create a new category
     * @see Admin_admin_new()
     * @param  string $args['name']        the name of the category to be created
     * @param  string $args['description'] the description of the category to be created
     * @return mixed  category id if create successful, false otherwise
     */
    public function createAction($args)
    {
        $this->checkCsrfToken();

        $category = FormUtil::getPassedValue('category', isset($args['category']) ? $args['category'] : null, 'POST');

        // Security check
        if (!SecurityUtil::checkPermission('Admin::Category', "$category[name]::", ACCESS_ADD)) {
            return LogUtil::registerPermissionError ();
        }

        $cid = ModUtil::apiFunc('Admin', 'admin', 'create',
                    array('name' => $category['name'],
                          'description' => $category['description']));

        if (is_numeric($cid)) {
            LogUtil::registerStatus($this->__('Done! Created new category.'));
        }

        $this->redirect(ModUtil::url('Admin', 'admin', 'view'));
    }

    /**
     * Modify a category
     * This is a standard function that is called whenever an administrator
     * wishes to modify an admin category
     * @param  int    $args['cid']      category id
     * @param  int    $args['objectid'] generic object id maps to cid if present
     * @return string HTML string
     */
    public function modifyAction($args)
    {
        $cid = FormUtil::getPassedValue('cid', isset($args['cid']) ? $args['cid'] : null, 'GET');
        $objectid = FormUtil::getPassedValue('objectid', isset($args['objectid']) ? $args['objectid'] : null, 'GET');

        if (!empty($objectid)) {
            $cid = $objectid;
        }

        $category = ModUtil::apiFunc('Admin', 'admin', 'get', array('cid' => $cid));
        if (empty($category)) {
            return LogUtil::registerError($this->__('Error! No such category found.'), 404);
        }

        if (!SecurityUtil::checkPermission('Admin::Category', "$category[name]::$cid", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        $this->view->assign('category', $category);

        return $this->view->fetch('admin/modify.tpl');
    }

    /**
     * This is a standard function that is called with the results of the
     * form supplied by template_admin_modify() to update a current item
     * @see Admin_admin_modify()
     * @param  int    $args['cid']         the id of the item to be updated
     * @param  int    $args['objectid']    generic object id maps to cid if present
     * @param  string $args['name']        the name of the category to be updated
     * @param  string $args['description'] the description of the item to be updated
     * @return bool   true if update successful, false otherwise
     */
    public function updateAction($args)
    {
        $this->checkCsrfToken();

        $category = FormUtil::getPassedValue('category', isset($args['category']) ? $args['category'] : null, 'POST');
        if (!empty($category['objectid'])) {
            $category['cid'] = $category['objectid'];
        }

        if (!SecurityUtil::checkPermission('Admin::Category', "$category[name]:$category[cid]", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError ();
        }

        $update = ModUtil::apiFunc('Admin', 'admin', 'update',
                    array('cid' => $category['cid'],
                          'name' => $category['name'],
                          'description' => $category['description']));

        if ($update) {
            // Success
            LogUtil::registerStatus($this->__('Done! Saved category.'));
        }

        $this->redirect(ModUtil::url('Admin', 'admin', 'view'));
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
     * developer.
     *
     * @param  int   $args['cid']          the id of the category to be deleted
     * @param  int   $args['objectid']     generic object id maps to cid if present
     * @param  bool  $args['confirmation'] confirmation that this item can be deleted
     * @return mixed HTML string if confirmation is null, true if delete successful, false otherwise
     */
    public function deleteAction($args)
    {
        $cid = FormUtil::getPassedValue('cid', isset($args['cid']) ? $args['cid'] : null, 'REQUEST');
        $objectid = FormUtil::getPassedValue('objectid', isset($args['objectid']) ? $args['objectid'] : null, 'REQUEST');
        $confirmation = FormUtil::getPassedValue('confirmation', null, 'POST');
        if (!empty($objectid)) {
            $cid = $objectid;
        }

        $category = ModUtil::apiFunc('Admin', 'admin', 'get', array('cid' => $cid));
        if (empty($category)) {
            return LogUtil::registerError($this->__('Error! No such category found.'), 404);
        }

        if (!SecurityUtil::checkPermission('Admin::Category', "$category[name]::$cid", ACCESS_DELETE)) {
            return LogUtil::registerPermissionError();
        }

        // Check for confirmation.
        if (empty($confirmation)) {
            // No confirmation yet - display a suitable form to obtain confirmation
            // of this action from the user
            return $this->view->assign('category', $category)
                              ->fetch('admin_admin_delete.tpl');
        }

        $this->checkCsrfToken();

        // delete category
        $delete = ModUtil::apiFunc('Admin', 'admin', 'delete', array('cid' => $cid));

        // Success
        if ($delete) {
            LogUtil::registerStatus($this->__('Done! Category deleted.'));
        }

        $this->redirect(ModUtil::url('Admin', 'admin', 'view'));
    }

    /**
     * Display main admin panel for a category
     *
     * @param  int    $args['acid'] the id of the category to be displayed
     * @return string HTML string
     */
    public function adminpanelAction($args)
    {
        if (!SecurityUtil::checkPermission('::', '::', ACCESS_EDIT)) {
            // suppress admin display - return to index.
            $this->redirect(System::getHomepageUrl());
        }

        if (!$this->getVar('ignoreinstallercheck') && System::getVar('development') == 0) {
            // check if the Zikula Recovery Console exists
            $zrcexists = file_exists('zrc.php');
            // check if upgrade scripts exist
            if ($zrcexists == true) {
                return $this->view->assign('zrcexists', $zrcexists)
                                  ->assign('adminpanellink', ModUtil::url('Admin','admin', 'adminpanel'))
                                  ->fetch('admin/warning.tpl');
            }
        }

        // Now prepare the display of the admin panel by getting the relevant info.

        // Get parameters from whatever input we need.
        $acid = FormUtil::getPassedValue('acid', (isset($args['acid']) ? $args['acid'] : null), 'GET');

        // cid isn't set, so go to the default category
        if (empty($acid)) {
            $acid = $this->getVar('startcategory');
        }

        // Add category menu to output
        $this->view->assign('menu', $this->categorymenuAction(array('acid' => $acid)));

        // Check to see if we have access to the requested category.
        if (!SecurityUtil::checkPermission("Admin::", "::$acid", ACCESS_ADMIN)) {
            $acid = -1;
        }

        // Get Details on the selected category
        if ($acid > 0) {
            $category = ModUtil::apiFunc('Admin', 'admin', 'get', array('cid' => $acid));
        } else {
            $category = null;
        }

        if (!$category) {
            // get the default category
            $acid = $this->getVar('startcategory');

            // Check to see if we have access to the requested category.
            if (!SecurityUtil::checkPermission("Admin::", "::$acid", ACCESS_ADMIN)) {
                return LogUtil::registerPermissionError(System::getHomepageUrl());
            }

            $category = ModUtil::apiFunc('Admin', 'admin', 'get', array('cid' => $acid));
        }

        // assign the category
        $this->view->assign('category', $category);

        $displayNameType = $this->getVar('displaynametype', 1);

        // get admin capable modules
        $adminmodules = ModUtil::getAdminMods();
        $adminlinks = array();
        foreach ($adminmodules as $adminmodule) {
            if (SecurityUtil::checkPermission("{$adminmodule['name']}::", 'ANY', ACCESS_EDIT)) {
                $catid = ModUtil::apiFunc('Admin', 'admin', 'getmodcategory',
                        array('mid' => ModUtil::getIdFromName($adminmodule['name'])));
                $order = ModUtil::apiFunc('Admin', 'admin', 'getSortOrder',
                        array('mid' => ModUtil::getIdFromName($adminmodule['name'])));
                if (($catid == $acid) || (($catid == false) && ($acid == $this->getVar('defaultcategory')))) {
                    $modinfo = ModUtil::getInfoFromName($adminmodule['name']);
                    $menutexturl = ModUtil::url($modinfo['name'], 'admin', 'main');
                    $modpath = ($modinfo['type'] == ModUtil::TYPE_SYSTEM) ? 'system' : 'modules';

                    if ($displayNameType == 1) {
                        $menutext = $modinfo['displayname'];
                    } elseif ($displayNameType == 2) {
                        $menutext = $modinfo['name'];
                    } elseif ($displayNameType == 3) {
                        $menutext = $modinfo['displayname'] . ' (' . $modinfo['name'] . ')';
                    }
                    $menutexttitle = $modinfo['description'];

                    $adminicon = ModUtil::getModuleImagePath($adminmodule['name']);

                    $adminlinks[] = array('menutexturl' => $menutexturl,
                            'menutext' => $menutext,
                            'menutexttitle' => $menutexttitle,
                            'modname' => $modinfo['name'],
                            'adminicon' => $adminicon,
                            'id' => $modinfo['id'],
                            'order'=> $order);
                }
            }
        }
        usort($adminlinks, '_sortAdminModsByOrder');
        $this->view->assign('adminlinks', $adminlinks);

        return $this->view->fetch('admin/adminpanel.tpl');
    }

    /**
     * This is a standard function to modify the configuration parameters of the
     * module.
     *
     * @return string HTML string
     */
    public function modifyconfigAction()
    {
        if (!SecurityUtil::checkPermission('Admin::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // get admin capable mods
        $adminmodules = ModUtil::getAdminMods();

        // Get all categories
        $categories = array();
        $items = ModUtil::apiFunc('Admin', 'admin', 'getall');
        foreach ($items as $item) {
            if (SecurityUtil::checkPermission('Admin::', "$item[name]::$item[cid]", ACCESS_READ)) {
                $categories[] = $item;
            }
        }
        $this->view->assign('categories', $categories);

        $modulecategories = array();
        foreach ($adminmodules as $adminmodule) {
            // Get the category assigned to this module
            $category = ModUtil::apiFunc('Admin', 'admin', 'getmodcategory',
                    array('mid' => ModUtil::getIdFromName($adminmodule['name'])));

            if ($category === false) {
                // it's not set, so we use the default category
                $category = $this->getVar('defaultcategory');
            }
            // output module category selection
            $modulecategories[] = array('displayname' => $adminmodule['displayname'],
                    'name' => $adminmodule['name'],
                    'category' => $category);
        }

        $this->view->assign('modulecategories', $modulecategories);

        // Return the output that has been generated by this function
        return $this->view->fetch('admin/modifyconfig.tpl');
    }

    /**
     * This is a standard function to update the configuration parameters of the
     * module given the information passed back by the modification form.
     *
     * @see Admin_admin_modifyconfig()
     * @param  int    $modulesperrow  the number of modules to display per row in the admin panel
     * @param  int    $admingraphic   switch for display of admin icons
     * @param  int    $modulename,... the id of the category to set for each module
     * @return string HTML string
     */
    public function updateconfigAction()
    {
        $this->checkCsrfToken();

        if (!SecurityUtil::checkPermission('Admin::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // get module vars
        $modvars = FormUtil::getPassedValue('modvars', null, 'POST');

        // check module vars
        $modvars['modulesperrow'] = isset($modvars['modulesperrow']) ? $modvars['modulesperrow'] : 5;
        if (!is_numeric($modvars['modulesperrow'])) {
            unset($modvars['modulesperrow']);
            LogUtil::registerError($this->__("Error! You must enter a number for the 'Modules per row' setting."));
        }
        $modvars['ignoreinstallercheck'] = isset($modvars['ignoreinstallercheck']) ? $modvars['ignoreinstallercheck'] : false;
        $modvars['itemsperpage'] = isset($modvars['itemsperpage']) ? $modvars['itemsperpage'] : 5;
        if (!is_numeric($modvars['itemsperpage'])) {
            unset($modvars['itemsperpage']);
            LogUtil::registerError($this->__("Error! You must enter a number for the 'Modules per page' setting."));
        }
        $modvars['admingraphic'] = isset($modvars['admingraphic']) ? $modvars['admingraphic'] : 0;
        $modvars['displaynametype'] = isset($modvars['displaynametype']) ? $modvars['displaynametype'] : 1;
        $modvars['startcategory'] = isset($modvars['startcategory']) ? $modvars['startcategory'] : 1;
        $modvars['defaultcategory'] = isset($modvars['defaultcategory']) ? $modvars['defaultcategory'] : 1;
        $modvars['admintheme'] = isset($modvars['admintheme']) ? $modvars['admintheme'] : null;

        // save module vars
        ModUtil::setVars('Admin', $modvars);

        // get admin modules
        $adminmodules = ModUtil::getAdminMods();
        $adminmods = FormUtil::getPassedValue('adminmods', null, 'POST');

        foreach ($adminmodules as $adminmodule) {
            $category = $adminmods[$adminmodule['name']];

            if ($category) {
                // Add the module to the category
                $result = ModUtil::apiFunc('Admin', 'admin', 'addmodtocategory',
                            array('module' => $adminmodule['name'],
                                  'category' => $category));

                if ($result == false) {
                    LogUtil::registerError($this->__('Error! Could not add module to module category.'));
                    $this->redirect(ModUtil::url('Admin', 'admin', 'view'));
                }
            }
        }

        // the module configuration has been updated successfuly
        LogUtil::registerStatus($this->__('Done! Saved module configuration.'));

        // This function generated no output, and so now it is complete we redirect
        // the user to an appropriate page for them to carry on their work
        $this->redirect(ModUtil::url('Admin', 'admin', 'view'));
    }

    /**
     * Main category menu.
     *
     * @return string HTML string
     */
    public function categorymenuAction($args)
    {
        // get the current category
        $acid = FormUtil::getPassedValue('acid', isset($args['acid']) ? $args['acid'] : $this->getVar('startcategory'), 'GET');

        // Get all categories
        $categories = array();
        $items = ModUtil::apiFunc('Admin', 'admin', 'getall');
        foreach ($items as $item) {
            if (SecurityUtil::checkPermission('Admin::', "$item[name]::$item[cid]", ACCESS_READ)) {
                $categories[] = $item;
            }
        }

        // get admin capable modules
        $adminmodules = ModUtil::getAdminMods();
        $adminlinks = array();

        foreach ($adminmodules as $adminmodule) {
            if (SecurityUtil::checkPermission("$adminmodule[name]::", '::', ACCESS_EDIT)) {
                $catid = ModUtil::apiFunc('Admin', 'admin', 'getmodcategory', array('mid' => $adminmodule['id']));
                $order = ModUtil::apiFunc('Admin', 'admin', 'getSortOrder',
                                          array('mid' => ModUtil::getIdFromName($adminmodule['name'])));
                $menutexturl = ModUtil::url($adminmodule['name'], 'admin', 'main');
                $menutext = $adminmodule['displayname'];
                $menutexttitle = $adminmodule['description'];
                $adminlinks[$catid][] = array('menutexturl' => $menutexturl,
                        'menutext' => $menutext,
                        'menutexttitle' => $menutexttitle,
                        'modname' => $adminmodule['name'],
                        'order' => $order,
                        'id' => $adminmodule['id']
                );
            }
        }

        foreach ($adminlinks as &$item) {
            usort($item, '_sortAdminModsByOrder');
        }

        $menuoptions = array();
        $possible_cids = array();
        $permission = false;

        if (isset($categories) && is_array($categories)) {
            foreach ($categories as $category) {
                // only categories containing modules where the current user has permissions will
                // be shown, all others will be hidden
                // admin will see all categories
                if ( (isset($adminlinks[$category['cid']]) && count($adminlinks[$category['cid']]) )
                        || SecurityUtil::checkPermission('.*', '.*', ACCESS_ADMIN) ) {
                    $menuoption = array('url'         => ModUtil::url('Admin','admin','adminpanel', array('acid' => $category['cid'])),
                            'title'       => $category['name'],
                            'description' => $category['description'],
                            'cid'         => $category['cid']);
                    if (isset($adminlinks[$category['cid']])) {
                        $menuoption['items'] = $adminlinks[$category['cid']];
                    } else {
                        $menuoption['items'] = array();
                    }
                    $menuoptions[$category['cid']] = $menuoption;
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

        $this->view->assign('currentcat', $acid);
        $this->view->assign('menuoptions', $menuoptions);

        // security analyzer and update checker warnings
        $notices = array();
        $notices['security'] = $this->_securityanalyzer();
        $notices['update'] = $this->_updatecheck();
        $notices['developer'] = $this->_developernotices();
        $this->view->assign('notices', $notices);

        return $this->view->fetch('includes/categorymenu.tpl');
    }

    /**
     * Open the admin container
     *
     */
    public function adminheaderAction()
    {
        return $this->view->fetch('includes/header.tpl');
    }

    /**
     * Close the admin container
     *
     */
    public function adminfooterAction()
    {
        return $this->view->fetch('includes/footer.tpl');
    }

    /**
     * display the module help page
     *
     */
    public function helpAction()
    {
        if (!SecurityUtil::checkPermission('Admin::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        return $this->view->fetch('admin/help.tpl');
    }

    /**
     * Get security analyzer data.
     *
     * @return array data
     */
    private function _securityanalyzer()
    {
        $data = array();

        // check for magic_quotes
        $data['magic_quotes_gpc'] = DataUtil::getBooleanIniValue('magic_quotes_gpc');

        // check for register_globals
        $data['register_globals'] = DataUtil::getBooleanIniValue('register_globals');

        // check for config.php beeing writable
        $data['config_php'] = (bool)is_writable('config/config.php');

        // check for .htaccess in temp directory
        $temp_htaccess = false;
        $tempDir = $GLOBALS['ZConfig']['System']['temp'];
        if ($tempDir) {
            // check if we have an absolute path which is possibly not within the document root
            $docRoot = System::serverGetVar('DOCUMENT_ROOT');
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

        $data['scactive']  = (bool)ModUtil::available('SecurityCenter');

        // check for outputfilter
        $data['useids'] = (bool)(ModUtil::available('SecurityCenter') && System::getVar('useids') == 1);
        $data['idssoftblock'] = System::getVar('idssoftblock');

        return $data;
    }

    /**
     * Check for updates
     *
     * @return data or false
     */
    private function _updatecheck($force=false)
    {
        if (!System::getVar('updatecheck')) {
            return array('update_show' => false);
        }

        $now = time();
        $lastChecked = (int)System::getVar('updatelastchecked');
        $checkInterval = (int)System::getVar('updatefrequency') * 86400;
        $updateversion = System::getVar('updateversion');

        if ($force == false && (($now - $lastChecked) < $checkInterval)) {
            // dont get an update because TTL not expired yet
            $onlineVersion = $updateversion;
        } else {
            $s = (extension_loaded('openssl') ? 's' : '');
            $onlineVersion = trim($this->_zcurl("http$s://update.zikula.org/cgi-bin/engine/checkcoreversion14.cgi"));
            if ($onlineVersion === false) {
                return array('update_show' => false);
            }
            System::setVar('updateversion', $onlineVersion);
            System::setVar('updatelastchecked', (int)time());
        }

        // if 1 then there is a later version available
        if (version_compare($onlineVersion, Zikula_Core::VERSION_NUM) == 1) {
            return array('update_show' => true,
                    'update_version' => $onlineVersion);
        } else {
            return array('update_show' => false);
        }
    }

    /**
     * Developer notices.
     *
     * @return data or false
     */
    private function _developernotices()
    {
        global $ZConfig;

        $modvars = ModUtil::getVar('Theme');

        $data = array();
        $data['devmode']                     = (bool) $ZConfig['System']['development'];

        if ($data['devmode'] == true) {
            $data['cssjscombine']                = $modvars['cssjscombine'];

            if ($modvars['render_compile_check']) {
                $data['render']['compile_check'] = array('state' => $modvars['render_compile_check'],
                        'title' => $this->__('Compile check'));
            }
            if ($modvars['render_force_compile']) {
                $data['render']['force_compile'] = array('state' => $modvars['render_force_compile'],
                        'title' => $this->__('Force compile'));
            }
            if ($modvars['render_cache']) {
                $data['render']['cache']         = array('state' => $modvars['render_cache'],
                        'title' => $this->__('Caching'));
            }
            if ($modvars['compile_check']) {
                $data['theme']['compile_check']  = array('state' => $modvars['compile_check'],
                        'title' => $this->__('Compile check'));
            }
            if ($modvars['force_compile']) {
                $data['theme']['force_compile']  = array('state' => $modvars['force_compile'],
                        'title' => $this->__('Force compile'));
            }
            if ($modvars['enablecache']) {
                $data['theme']['cache']          = array('state' => $modvars['enablecache'],
                        'title' => $this->__('Caching'));
            }
        }

        return $data;
    }

    /**
     * Zikula curl
     *
     * This function is internal for the time being and may be extended to be a proper library
     * or find an alternative solution later.
     *
     * @param  string $url
     * @param  ing    $timeout default=5
     * @return mixed, false or string
     */
    private function _zcurl($url, $timeout=5)
    {
        $urlArray = parse_url($url);
        $data = '';
        $userAgent = 'Zikula/' . Zikula_Core::VERSION_NUM;
        $ref = System::getBaseUrl();
        $port = (($urlArray['scheme'] == 'https') ? 443 : 80);
        if (ini_get('allow_url_fopen')) {
            // handle SSL connections
            $path_query = (isset($urlArray['query']) ? $urlArray['path'] . $urlArray['query'] : $urlArray['path']);
            $host = ($port==443 ? "ssl://$urlArray[host]" : $urlArray['host']);
            $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
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
            if (!ini_get('safe_mode') && !ini_get('open_basedir')) {
                // This option doesnt work in safe_mode or with open_basedir set in php.ini
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            }
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            $data = curl_exec($ch);
            if (!$data && $port=443) {
                // retry non ssl
                $url = str_replace('https://', 'http://', $url);
                curl_setopt($ch, CURLOPT_URL, "$url?");
                $data = @curl_exec($ch);
            }
            //$headers = curl_getinfo($ch);
            curl_close($ch);

            return $data;
        } else {
            return false;
        }
    }
}

function _sortAdminModsByOrder($a,$b)
{
    if ((int)$a['order'] == (int)$b['order']) {
        return strcmp($a['modname'], $b['modname']);
    }
    if((int)$a['order']  > (int)$b['order']) return 1;
    if((int)$a['order']  < (int)$b['order']) return -1;
}
