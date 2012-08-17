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

/**
 * Administrative API functions for the Extensions module.
 */
class Extensions_Api_Admin extends Zikula_AbstractApi
{
    /**
     * Update module information.
     *
     * @param array $args All parameters passed to this function.
     *                      numeric $args['id'] The id number of the module.
     *
     * @return array An associative array containing the module information for the specified module id.
     */
    public function modify($args)
    {
        return DBUtil::selectObjectByID('modules', $args['id'], 'id');
    }

    /**
     * Update module information.
     *
     * @param array $args All parameters passed to this function.
     *                      numeric $args['id']          The id number of the module to update.
     *                      string  $args['displayname'] The new display name of the module.
     *                      string  $args['description'] The new description of the module.
     *
     * @return boolean True on success, false on failure.
     */
    public function update($args)
    {
        // Argument check
        if (!isset($args['id']) || !is_numeric($args['id']) ||
                !isset($args['displayname']) ||
                !isset($args['description']) ||
                !isset($args['url'])) {
            return LogUtil::registerArgsError();
        }

        // Security check
        if (!SecurityUtil::checkPermission('Extensions::', "::$args[id]", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // check for duplicate display names
        // get the module info for the module being updated
        $moduleinforeal = ModUtil::getInfo($args['id']);
        // validate URL
        $moduleinfourl = ModUtil::getInfoFromName($args['url']);
        // If the two real module name don't match then the new display name can't be used
        if ($moduleinfourl && $moduleinfourl['name'] != $moduleinforeal['name']) {
            return LogUtil::registerError($this->__('Error! Could not save the module URL information. A duplicate module URL was detected.'));
        }

        if (empty($args['url'])) {
            return LogUtil::registerError($this->__('Error! Module URL is a required field, please enter a unique name.'));
        }

        if (empty($args['displayname'])) {
            return LogUtil::registerError($this->__('Error! Module URL is a required field, please enter a unique name.'));
        }

        // Rename operation
        $obj = array('id'          => $args['id'],
                     'displayname' => $args['displayname'],
                     'description' => $args['description'],
                     'url'         => $args['url']);

        if (!DBUtil::updateObject($obj, 'modules')) {
            return LogUtil::registerError($this->__('Error! Could not save your changes.'));
        }

        return true;
    }

    /**
     * Obtain a list of modules.
     *
     * @param array $args All parameters passed to this function.
     *                      integer $args['startnum'] The number of the module at which to start the list (for paging); optional,
     *                                                  defaults to 1.
     *                      integer $args['numitems'] The number of the modules to return in the list (for paging); optional, defaults to
     *                                                  -1, which returns modules starting at the specified number without limit.
     *                      integer $args['state']    Filter the list by this state; optional.
     *                      integer $args['type']     Filter the list by this type; optional.
     *                      string  $args['letter']   Filter the list by module names beginning with this letter; optional.
     *
     * @return array An associative array of known modules.
     */
    public function listmodules($args)
    {
        // Security check
        if (!System::isInstalling()) {
            if (!SecurityUtil::checkPermission('Extensions::', '::', ACCESS_ADMIN)) {
                return LogUtil::registerPermissionError();
            }
        }

        // Optional arguments.
        $startnum = (empty($args['startnum']) || $args['startnum'] < 0) ? 1 : (int)$args['startnum'];
        $numitems = (empty($args['numitems']) || $args['numitems'] < 0) ? -1 : (int)$args['numitems'];
        if ($this->serviceManager['multisites.enabled'] == 1) {
            $state = (empty($args['state']) || $args['state'] < -1 || $args['state'] > ModUtil::STATE_NOTALLOWED) ? 0 : (int)$args['state'];
        } else {
            $state = (empty($args['state']) || $args['state'] < -1 || $args['state'] > ModUtil::STATE_UPGRADED) ? 0 : (int)$args['state'];
        }

        // for incompatible versions of the modules with the core
        $state = $args['state'];

        $type    = (empty($args['type']) || $args['type'] < 0 || $args['type'] > ModUtil::TYPE_SYSTEM) ? 0 : (int)$args['type'];
        $sort    = empty($args['sort']) ? null : (string)$args['sort'];
        $sortdir = isset($args['sortdir']) && $args['sortdir'] ? $args['sortdir'] : 'ASC';

        // Obtain information
        $dbtable = DBUtil::getTables();
        $modulescolumn = $dbtable['modules_column'];

        // filter my first letter of module
        if (isset($args['letter']) && !empty($args['letter'])) {
            $where[] = "$modulescolumn[name] LIKE '" . DataUtil::formatForStore($args['letter']) . "%' OR " . "$modulescolumn[name] LIKE '" . DataUtil::formatForStore(strtolower($args['letter'])) . "%'";
        }

        if ($type != 0) {
            $where[] = "$modulescolumn[type] = '" . (int)DataUtil::formatForStore($type) . "'";
        }

        // filter by module state
        switch ($state) {
            case ModUtil::STATE_UNINITIALISED:
            case ModUtil::STATE_INACTIVE:
            case ModUtil::STATE_ACTIVE:
            case ModUtil::STATE_MISSING:
            case ModUtil::STATE_UPGRADED:
            case ModUtil::STATE_NOTALLOWED:
            case ModUtil::STATE_INVALID:
                $where[] = "$modulescolumn[state] = '" . DataUtil::formatForStore($state) . "'";
                break;
        }

        if ($state == 10) {
            $where[] = "$modulescolumn[state] > 10";
        }

        // generate where clause
        $wheresql = '';
        if (isset($where) && is_array($where)) {
            $wheresql = 'WHERE ' . implode(' AND ', $where);
        }

        if ($sort == 'displayname') {
            $orderBy = "ORDER BY UPPER($modulescolumn[displayname]) $sortdir";
        } else {
            $orderBy = "ORDER BY UPPER($modulescolumn[name]) $sortdir";
        }

        $objArray = DBUtil::selectObjectArray('modules', $wheresql, $orderBy, $startnum - 1, $numitems);

        if ($objArray === false) {
            return LogUtil::registerError($this->__('Error! Could not load data.'));
        }

        foreach ($objArray as $key => $object) {
            $objArray[$key]['capabilities'] = unserialize($object['capabilities']);
        }

        return $objArray;
    }

    /**
     * Set the state of a module.
     *
     * @param array $args All parameters passed to this function.
     *                      numeric $args['id']    The module id.
     *                      integer $args['state'] The new state.
     *
     * @return boolean True if successful, false otherwise.
     */
    public function setState($args)
    {
        // Argument check
        if (!isset($args['id']) || !is_numeric($args['id']) || !isset($args['state'])) {
            return LogUtil::registerArgsError();
        }

        // Security check
        if (!System::isInstalling()) {
            if (!SecurityUtil::checkPermission('Extensions::', '::', ACCESS_EDIT)) {
                return LogUtil::registerPermissionError();
            }
        }

        // Set state
        $result = DBUtil::selectObjectByID('modules', $args['id'], 'id', null, null, false);
        if (empty($result)) {
            return false;
        }

        if ($result === false) {
            return LogUtil::registerPermissionError();
        }

        $name = $result['name'];
        $directory = $result['directory'];
        $oldstate = $result['state'];

        $modinfo = ModUtil::getInfo($args['id']);
        // Check valid state transition
        switch ($args['state']) {
            case ModUtil::STATE_UNINITIALISED:
                if ($this->serviceManager['multisites.enabled'] == 1) {
                    if (!SecurityUtil::checkPermission('Extensions::', '::', ACCESS_ADMIN)) {
                        return LogUtil::registerError($this->__('Error! Invalid module state transition.'));
                    }
                }
                break;
            case ModUtil::STATE_INACTIVE:
                break;
            case ModUtil::STATE_ACTIVE:
                break;
            case ModUtil::STATE_MISSING:
                break;
            case ModUtil::STATE_UPGRADED:
                if ($oldstate == ModUtil::STATE_UNINITIALISED) {
                    return LogUtil::registerError($this->__('Error! Invalid module state transition.'));
                }
                break;
        }

        $obj = array('id' => $args['id'], 'state' => $args['state']);
        if (!DBUtil::updateObject($obj, 'modules')) {
            return false;
        }

        // State change, so update the ModUtil::available-info for this module.
        ModUtil::available($modinfo['name'], true);

        return true;
    }

    /**
     * Remove a module.
     *
     * @param array $args All parameters sent to this function.
     *                      numeric $args['id']                 The id of the module.
     *                      boolean $args['removedependents']   Remove any modules dependent on this module (default: false).
     *                      boolean $args['interactive_remove'] Whether to operat in interactive mode or not.
     *
     * @return boolean True on success, false on failure.
     */
    public function remove($args)
    {
        // Argument check
        if (!isset($args['id']) || !is_numeric($args['id'])) {
            return LogUtil::registerArgsError();
        }

        if (!isset($args['removedependents']) || !is_bool($args['removedependents'])) {
            $removedependents = false;
        } else {
            $removedependents = true;
        }

        // Security check
        if (!SecurityUtil::checkPermission('Extensions::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Get module information
        $modinfo = ModUtil::getInfo($args['id']);
        if (empty($modinfo)) {
            return LogUtil::registerError($this->__('Error! No such module ID exists.'));
        }

        switch ($modinfo['state']) {
            case ModUtil::STATE_NOTALLOWED:
                return LogUtil::registerError($this->__f('Error! No permission to upgrade %s.', $modinfo['name']));
                break;
        }

        $osdir = DataUtil::formatForOS($modinfo['directory']);
        $modpath = ($modinfo['type'] == ModUtil::TYPE_SYSTEM) ? 'system' : 'modules';

        $oomod = ModUtil::isOO($modinfo['name']);

        if ($oomod) {
            ZLoader::addAutoloader($osdir, "$modpath/$osdir/lib");
        }

        $version = Extensions_Util::getVersionMeta($osdir, $modpath);

        $bootstrap = "$modpath/$osdir/bootstrap.php";
        if (file_exists($bootstrap)) {
            include_once $bootstrap;
        }

        if ($modinfo['type'] == ModUtil::TYPE_MODULE) {
            if (is_dir("modules/$osdir/locale")) {
                ZLanguage::bindModuleDomain($modinfo['name']);
            }
        }

        // call any module delete hooks
        if (System::isLegacyMode() && !$oomod) {
            ModUtil::callHooks('module', 'remove', $modinfo['name'], array('module' => $modinfo['name']));
        }

        // Get module database info
        ModUtil::dbInfoLoad($modinfo['name'], $osdir);

        // Module deletion function. Only execute if the module is initialised.
        if ($modinfo['state'] != ModUtil::STATE_UNINITIALISED) {
            if (!$oomod && file_exists($file = "$modpath/$osdir/pninit.php")) {
                if (!include_once($file)) {
                    LogUtil::registerError($this->__f("Error! Could not load a required file: '%s'.", $file));
                }
            }

            if ($oomod) {
                $className = ucwords($modinfo['name']) . '_Installer';
                $reflectionInstaller = new ReflectionClass($className);
                if (!$reflectionInstaller->isSubclassOf('Zikula_AbstractInstaller')) {
                    LogUtil::registerError($this->__f("%s must be an instance of Zikula_AbstractInstaller", $className));
                }
                $installer = $reflectionInstaller->newInstanceArgs(array($this->serviceManager));
                $interactiveClass = ucwords($modinfo['name']) . '_Controller_InteractiveInstaller';
                $interactiveController = null;
                if (class_exists($interactiveClass)) {
                    $reflectionInteractive = new ReflectionClass($interactiveClass);
                    if (!$reflectionInteractive->isSubclassOf('Zikula_Controller_AbstractInteractiveInstaller')) {
                        LogUtil::registerError($this->__f("%s must be an instance of Zikula_Controller_AbstractInteractiveInstaller", $className));
                    }
                    $interactiveController = $reflectionInteractive->newInstance($this->serviceManager);
                }
            }

            // perform the actual deletion of the module
            $func = ($oomod) ? array($installer, 'uninstall') : $modinfo['name'] . '_delete';
            $interactive_func = ($oomod) ? array($interactiveController, 'uninstall') : $modinfo['name'] . '_init_interactivedelete';

            // allow bypass of interactive removal during a new installation only.
            if (System::isInstalling() && is_callable($interactive_func) && !is_callable($func)) {
                return; // return void here
            }

            if ((isset($args['interactive_remove']) && $args['interactive_remove'] == false) && is_callable($interactive_func)) {
                if (is_array($interactive_func)) {
                    // This must be an OO controller since callable is an array.
                    // Because interactive installers extend the Zikula_AbstractController, is_callable will always return true because of the __call()
                    // so we must check if the method actually exists by reflection - drak
                    if ($reflectionInteractive->hasMethod('upgrade')) {
                        SessionUtil::setVar('interactive_remove', true);

                        return call_user_func($interactive_func);
                    }
                } else {
                    // tnis is enclosed in the else so that if both conditions fail, execution will pass onto the non-interactive execution below.
                    SessionUtil::setVar('interactive_remove', true);

                    return call_user_func($interactive_func);
                }
            }

            // non-interactive
            if (is_callable($func)) {
                if (call_user_func($func) != true) {
                    return false;
                }
            }
        }

        // Remove variables and module
        // Delete any module variables that the module cleanup function might
        // have missed
        DBUtil::deleteObjectByID('module_vars', $modinfo['name'], 'modname');

        // clean up any hooks activated for this module
        if (System::isLegacyMode()) {
            DBUtil::deleteObjectByID('hooks', $modinfo['name'], 'tmodule');
        }

        if ($oomod) {
            HookUtil::unregisterProviderBundles($version->getHookProviderBundles());
            HookUtil::unregisterSubscriberBundles($version->getHookSubscriberBundles());
            EventUtil::unregisterPersistentModuleHandlers($modinfo['name']);
        }

        // remove the entry from the modules table
        if ($this->serviceManager['multisites.enabled'] == 1) {
            // who can access to the mainSite can delete the modules in any other site
            $canDelete = (($this->serviceManager['multisites.mainsiteurl'] == FormUtil::getPassedValue('sitedns', null, 'GET') && $this->serviceManager['multisites.based_on_domains'] == 0) || ($this->serviceManager['multisites.mainsiteurl'] == $_SERVER['HTTP_HOST'] && $this->serviceManager['multisites.based_on_domains'] == 1)) ? 1 : 0;
            //delete the module infomation only if it is not allowed, missign or invalid
            if ($canDelete == 1 || $modinfo['state'] == ModUtil::STATE_NOTALLOWED || $modinfo['state'] == ModUtil::STATE_MISSING || $modinfo['state'] == ModUtil::STATE_INVALID) {
                // remove the entry from the modules table
                DBUtil::deleteObjectByID('modules', $args['id'], 'id');
            } else {
                //set state as uninnitialised
                ModUtil::apiFunc('modules', 'admin', 'setstate', array('id' => $args['id'], 'state' => ModUtil::STATE_UNINITIALISED));
            }
        } else {
            DBUtil::deleteObjectByID('modules', $args['id'], 'id');
        }

        $event = new Zikula_Event('installer.module.uninstalled', null, $modinfo);
        $this->eventManager->notify($event);

        return true;
    }

    /**
     * Scan the file system for modules.
     *
     * This function scans the file system for modules and returns an array with all (potential) modules found.
     * This information is used to regenerate the module list.
     *
     * @return array An array of modules found in the file system.
     */
    public function getfilemodules()
    {
        // Security check
        if (!System::isInstalling()) {
            if (!SecurityUtil::checkPermission('Extensions::', '::', ACCESS_ADMIN)) {
                return LogUtil::registerPermissionError();
            }
        }

        // Get all modules on filesystem
        $filemodules = array();

        // set the paths to search
        $rootdirs = array('system' => ModUtil::TYPE_SYSTEM, 'modules' => ModUtil::TYPE_MODULE);

        foreach ($rootdirs as $rootdir => $moduletype) {
            if (is_dir($rootdir)) {
                $dirs = FileUtil::getFiles($rootdir, false, true, null, 'd');

                foreach ($dirs as $dir) {
                    $oomod = false;
                    // register autoloader
                    if (is_dir("$rootdir/$dir/lib")) {
                        ZLoader::addAutoloader($dir, "$rootdir/$dir/lib");
                        $oomod = true;
                    }

                    // loads the gettext domain for 3rd party modules
                    if ($rootdir == 'modules' && is_dir("modules/$dir/locale")) {
                        // This is required here since including pnversion automatically executes the pnversion code
                        // this results in $this->__() caching the result before the domain is bounded.  Will not occur in zOO
                        // since loading is self contained in each zOO application.
                        ZLanguage::bindModuleDomain($dir);
                    }

                    try {
                        $modversion = Extensions_Util::getVersionMeta($dir, $rootdir);
                    } catch (Exception $e) {
                        LogUtil::registerError($e->getMessage());
                        continue;
                    }

                    if (!isset($modversion['capabilities'])) {
                        $modversion['capabilities'] = array();
                    }

                    $name = $dir;

                    // Get the module version
                    if (!$modversion instanceof Zikula_AbstractVersion) {
                        if (isset($modversion['profile']) && $modversion['profile']) {
                            $modversion['capabilities']['profile'] = '1.0';
                        }
                        if (isset($modversion['message']) && $modversion['message']) {
                            $modversion['capabilities']['message'] = '1.0';
                        }
                        // Work out if admin-capable
                        if (file_exists("$rootdir/$dir/pnadmin.php") || is_dir("$rootdir/$dir/pnadmin")) {
                            $modversion['capabilities']['admin'] = '1.0';
                        }

                        // Work out if user-capable
                        if (file_exists("$rootdir/$dir/pnuser.php") || is_dir("$rootdir/$dir/pnuser")) {
                            $modversion['capabilities']['user'] = '1.0';
                        }
                    } elseif ($oomod) {
                        // Work out if admin-capable
                        if (file_exists("$rootdir/$dir/lib/$dir/Controller/Admin.php")) {
                            $caps = $modversion['capabilities'];
                            $caps['admin'] = array('version' => '1.0');
                            $modversion['capabilities'] = $caps;
                        }

                        // Work out if user-capable
                        if (file_exists("$rootdir/$dir/lib/$dir/Controller/User.php")) {
                            $caps = $modversion['capabilities'];
                            $caps['user'] = array('version' => '1.0');
                            $modversion['capabilities'] = $caps;
                        }
                    }

                    $version = $modversion['version'];
                    $description = $modversion['description'];

                    if (isset($modversion['displayname']) && !empty($modversion['displayname'])) {
                        $displayname = $modversion['displayname'];
                    } else {
                        $displayname = $modversion['name'];
                    }

                    $capabilities = serialize($modversion['capabilities']);

                    // bc for urls
                    if (isset($modversion['url']) && !empty($modversion['url'])) {
                        $url = $modversion['url'];
                    } else {
                        $url = $displayname;
                    }

                    if (isset($modversion['securityschema']) && is_array($modversion['securityschema'])) {
                        $securityschema = serialize($modversion['securityschema']);
                    } else {
                        $securityschema = serialize(array());
                    }

                    $core_min = isset($modversion['core_min']) ? $modversion['core_min'] : '';
                    $core_max = isset($modversion['core_max']) ? $modversion['core_max'] : '';
                    $oldnames = isset($modversion['oldnames']) ? $modversion['oldnames'] : '';

                    if (isset($modversion['dependencies']) && is_array($modversion['dependencies'])) {
                        $moddependencies = serialize($modversion['dependencies']);
                    } else {
                        $moddependencies = serialize(array());
                    }
                    //if (!isset($args['name'])) {
                        $filemodules[$name] = array(
                                'directory'       => $dir,
                                'name'            => $name,
                                'type'            => $moduletype,
                                'displayname'     => $displayname,
                                'url'             => $url,
                                'oldnames'        => $oldnames,
                                'version'         => $version,
                                'capabilities'    => $capabilities,
                                'description'     => $description,
                                'securityschema'  => $securityschema,
                                'dependencies'    => $moddependencies,
                                'core_min'        => $core_min,
                                'core_max'        => $core_max,
                        );

                    // important: unset modversion and modtype, otherwise the
                    // following modules will have some values not defined in
                    // the next version files to be read
                    unset($modversion);
                    unset($modtype);
                }
            }
        }

        return $filemodules;
    }

    /**
     * Regenerate modules list.
     *
     * @param array $args All parameters passed to this function.
     *                      array $args['filemodules'] An array of modules in the filesystem, as would be returned by
     *                                                  {@link getfilemodules()}; optional, defaults to the results of
     *                                                  $this->getfilemodules().
     *
     * @return boolean True on success, false on failure.
     */
    public function regenerate($args)
    {
        // Security check
        if (!System::isInstalling()) {
            if (!SecurityUtil::checkPermission('Extensions::', '::', ACCESS_ADMIN)) {
                return LogUtil::registerPermissionError();
            }
        }

        // Argument check
        if (!isset($args['filemodules']) || !is_array($args['filemodules'])) {
            return LogUtil::registerArgsError();
        }

        // default action
        $filemodules = $args['filemodules'];
        $defaults = (isset($args['defaults']) ? $args['defaults'] : false);

        // Get all modules in DB
        $dbmodules = DBUtil::selectObjectArray('modules', '', '', -1, -1, 'name');

        if (!$dbmodules) {
            return LogUtil::registerError($this->__('Error! Could not load data.'));
        }

        // build a list of found modules and dependencies
        $module_names = array();
        $moddependencies = array();
        foreach ($filemodules as $modinfo) {
            $module_names[] = $modinfo['name'];
            if (isset($modinfo['dependencies']) && !empty($modinfo['dependencies'])) {
                $moddependencies[$modinfo['name']] = unserialize($modinfo['dependencies']);
            }
        }

        // see if any modules have changed name since last generation
        foreach ($filemodules as $name => $modinfo) {
            if (isset($modinfo['oldnames']) && !empty($modinfo['oldnames'])) {
                $tables = DBUtil::getTables();
                foreach ($dbmodules as $dbname => $dbmodinfo) {
                    if (in_array($dbmodinfo['name'], (array)$modinfo['oldnames'])) {
                        // migrate its modvars
                        $cols = $tables['module_vars_column'];
                        $save = array('modname' => $modinfo['name']);
                        DBUtil::updateObject($save, 'module_vars', "{$cols['modname']} = '$dbname'");

                        // rename the module register
                        $save = $dbmodules[$dbname];
                        $save['name'] = $modinfo['name'];
                        unset($dbmodules[$dbname]);
                        $dbname = $modinfo['name'];
                        $dbmodules[$dbname] = $save;
                        DBUtil::updateObject($dbmodules[$dbname], 'modules');

                        // rename hooks in the hooks table.
                        $hooksColumns = $tables['hooks_column'];
                        $hooks = DBUtil::selectObjectArray('hooks', "$hooksColumns[smodule] = '$save[name]'");
                        if ($hooks) {
                            foreach ($hooks as $hook) {
                                $hook['smodule'] = $dbmodinfo['name'];
                                DBUtil::updateObject($hook, 'hooks');
                            }
                        }

                        $hooks = DBUtil::selectObjectArray('hooks', "$hooksColumns[tmodule] = '$save[name]'");
                        if ($hooks) {
                            foreach ($hooks as $hook) {
                                $hook['tmodule'] = $dbmodinfo['name'];
                                DBUtil::updateObject($hook, 'hooks');
                            }
                        }

                        DBUtil::deleteObjectByID('hooks', $modinfo['name'], 'tmodule');
                    }
                }
                unset($tables);
            }

            if (isset($dbmodules[$name]) && $dbmodules[$name]['state'] > 10) {
                $dbmodules[$name]['state'] = $dbmodules[$name]['state'] - 20;
                $this->setState(array('id' => $dbmodules[$name]['id'], 'state' => $dbmodules[$name]['state']));
            }

            if (isset($dbmodules[$name]['id'])) {
                $modinfo['id'] = $dbmodules[$name]['id'];
                if ($dbmodules[$name]['state'] != ModUtil::STATE_UNINITIALISED && $dbmodules[$name]['state'] != ModUtil::STATE_INVALID) {
                    unset($modinfo['version']);
                }
                if (!$defaults) {
                    unset($modinfo['displayname']);
                    unset($modinfo['description']);
                    unset($modinfo['url']);
                }
                DBUtil::updateObject($modinfo, 'modules');
            }

            // check core version is compatible with current
            $minok = 0;
            $maxok = 0;
            // strip any -dev, -rcN etc from version number
            $coreVersion = preg_replace('#(\d+\.\d+\.\d+).*#', '$1', Zikula_Core::VERSION_NUM);
            if (!empty($filemodules[$name]['core_min'])) {
                $minok = version_compare($coreVersion, $filemodules[$name]['core_min']);
            }
            if (!empty($filemodules[$name]['core_max'])) {
                $maxok = version_compare($filemodules[$name]['core_max'], $coreVersion);
            }

            if ($minok == -1 || $maxok == -1) {
                $dbmodules[$name]['state'] = $dbmodules[$name]['state'] + 20;
                $this->setState(array('id' => $dbmodules[$name]['id'], 'state' => $dbmodules[$name]['state']));
            }
            if (isset($dbmodules[$name]['state'])) {
                $filemodules[$name]['state'] = $dbmodules[$name]['state'];
            }
        }

        // See if we have lost any modules since last generation
        foreach ($dbmodules as $name => $modinfo) {
            if (!in_array($name, $module_names)) {
                $result = DBUtil::selectObjectByID('modules', $name, 'name');

                if ($result === false) {
                    return LogUtil::registerError($this->__('Error! Could not load data.'));
                }

                if (empty($result)) {
                    die($this->__('Error! Could not retrieve module ID.'));
                }

                if ($dbmodules[$name]['state'] == ModUtil::STATE_INVALID) {
                    // module was invalid and now it was removed, delete it
                    $this->remove(array('id'   => $dbmodules[$name]['id']));
                } elseif ($dbmodules[$name]['state'] == ModUtil::STATE_UNINITIALISED) {
                    // module was uninitialised and subsequently removed, delete it
                    $this->remove(array('id'   => $dbmodules[$name]['id']));
                } else {
                    // Set state of module to 'missing'
                    $this->setState(array('id' => $result['id'], 'state' => ModUtil::STATE_MISSING));
                }
                unset($dbmodules[$name]);
            }
        }

        // See if we have gained any modules since last generation,
        // or if any current modules have been upgraded
        foreach ($filemodules as $name => $modinfo) {
            if (empty($dbmodules[$name])) {
                // New module
                // RNG: set state to invalid if we can't determine an ID
                $modinfo['state'] = ModUtil::STATE_UNINITIALISED;
                if (!$modinfo['version']) {
                    $modinfo['state'] = ModUtil::STATE_INVALID;
                }
                if ($this->serviceManager['multisites.enabled'] == 1) {
                    // only the main site can regenerate the modules list
                    if (($this->serviceManager['multisites.mainsiteurl'] == FormUtil::getPassedValue('sitedns', null, 'GET') && $this->serviceManager['multisites.based_on_domains'] == 0) || ($this->serviceManager['multisites.mainsiteurl'] == $_SERVER['HTTP_HOST'] && $this->serviceManager['multisites.based_on_domains'] == 1)) {
                        DBUtil::insertObject($modinfo, 'modules');
                    }
                } else {
                    DBUtil::insertObject($modinfo, 'modules');
                }
            } else {
                // module is in the db already
                if ($dbmodules[$name]['state'] == ModUtil::STATE_MISSING) {
                    // module was lost, now it is here again
                    $this->setState(array('id' => $dbmodules[$name]['id'], 'state' => ModUtil::STATE_INACTIVE));
                } elseif ($dbmodules[$name]['state'] == ModUtil::STATE_INVALID && $modinfo['version']) {
                    // module was invalid, now it is valid
                    $modinfo = array_merge($modinfo, array('id' => $dbmodules[$name]['id'], 'state' => ModUtil::STATE_UNINITIALISED));
                    DBUtil::updateObject($modinfo, 'modules');
                }
                if ($dbmodules[$name]['version'] != $modinfo['version']) {
                    if ($dbmodules[$name]['state'] != ModUtil::STATE_UNINITIALISED &&
                            $dbmodules[$name]['state'] != ModUtil::STATE_INVALID) {
                        $this->setState(array('id' => $dbmodules[$name]['id'], 'state' => ModUtil::STATE_UPGRADED));
                    }
                }
            }
        }

        // now clear re-load the dependencies table with all current dependencies
        DBUtil::truncateTable('module_deps');
        // loop round dependences adding the module id - we do this now rather than
        // earlier since we won't have the id's for new modules at that stage
        $dependencies = array();
        ModUtil::flushCache();
        foreach ($moddependencies as $modname => $moddependency) {
            $modid = ModUtil::getIdFromName($modname);
            // each module may have multiple dependencies
            foreach ($moddependency as $dependency) {
                $dependency['modid'] = $modid;
                $dependencies[] = $dependency;
            }
        }
        DBUtil::insertObjectArray($dependencies, 'module_deps');

        return true;
    }

    /**
     * Initialise a module.
     *
     * @param array $args All parameters passed to this function.
     *                      numeric $args['id']               The module ID.
     *                      boolean $args['interactive_mode'] Perform the initialization in interactive mode or not.
     *
     * @return boolean|void True on success, false on failure, or null when we bypassed the installation;
     */
    public function initialise($args)
    {
        // Argument check
        if (!isset($args['id']) || !is_numeric($args['id'])) {
            return LogUtil::registerArgsError();
        }

        // Get module information
        $modinfo = ModUtil::getInfo($args['id']);
        if (empty($modinfo)) {
            return LogUtil::registerError($this->__('Error! No such module ID exists.'));
        }

        switch ($modinfo['state']) {
            case ModUtil::STATE_NOTALLOWED:
                return LogUtil::registerError($this->__f('Error! No permission to install %s.', $modinfo['name']));
                break;
            default:
                if ($modinfo['state'] > 10) {
                    return LogUtil::registerError($this->__f('Error! %s is not compatible with this version of Zikula.', $modinfo['name']));
                }
        }

        // Get module database info
        $osdir = DataUtil::formatForOS($modinfo['directory']);
        ModUtil::dbInfoLoad($modinfo['name'], $osdir);
        $modpath = ($modinfo['type'] == ModUtil::TYPE_SYSTEM) ? 'system' : 'modules';

        // load module maintainence functions
        $oomod = ModUtil::isOO($modinfo['name']);

        if ($oomod) {
            ZLoader::addAutoloader($osdir, "$modpath/$osdir/lib");
        }

        $bootstrap = "$modpath/$osdir/bootstrap.php";
        if (file_exists($bootstrap)) {
            include_once $bootstrap;
        }

        if ($modinfo['type'] == ModUtil::TYPE_MODULE) {
            if (is_dir("modules/$osdir/locale")) {
                ZLanguage::bindModuleDomain($modinfo['name']);
            }
        }

        if (!$oomod && file_exists($file = "$modpath/$osdir/pninit.php")) {
            if (!include_once($file)) {
                LogUtil::registerError($this->__f("Error! Could not load a required file: '%s'.", $file));
            }
        }

        if ($oomod) {
            $className = ucwords($modinfo['name']) . '_Installer';
            $reflectionInstaller = new ReflectionClass($className);
            if (!$reflectionInstaller->isSubclassOf('Zikula_AbstractInstaller')) {
                LogUtil::registerError($this->__f("%s must be an instance of Zikula_AbstractInstaller", $className));
            }
            $installer = $reflectionInstaller->newInstance($this->serviceManager);
            $interactiveClass = ucwords($modinfo['name']) . '_Controller_InteractiveInstaller';
            $interactiveController = null;
            if (class_exists($interactiveClass)) {
                $reflectionInteractive = new ReflectionClass($interactiveClass);
                if (!$reflectionInteractive->isSubclassOf('Zikula_Controller_AbstractInteractiveInstaller')) {
                    LogUtil::registerError($this->__f("%s must be an instance of Zikula_Controller_AbstractInteractiveInstaller", $className));
                }
                $interactiveController = $reflectionInteractive->newInstance($this->serviceManager);
            }
        }

        // perform the actual install of the module
        // system or module
        $func = ($oomod) ? array($installer, 'install') : $modinfo['name'] . '_init';
        $interactive_func = ($oomod) ? array($interactiveController, 'install') : $modinfo['name'] . '_init_interactiveinit';

        // allow bypass of interactive install during a new installation only.
        if (System::isInstalling() && is_callable($interactive_func) && !is_callable($func)) {
            return; // return void here
        }

        if (!System::isInstalling() && isset($args['interactive_init']) && ($args['interactive_init'] == false) && is_callable($interactive_func)) {
            if (is_array($interactive_func)) {
                // This must be an OO controller since callable is an array.
                // Because interactive installers extend the Zikula_AbstractController, is_callable will always return true because of the __call()
                // so we must check if the method actually exists by reflection - drak
                if ($reflectionInteractive->hasMethod('install')) {
                    SessionUtil::setVar('interactive_init', true);

                    return call_user_func($interactive_func);
                }
            } else {
                // tnis is enclosed in the else so that if both conditions fail, execution will pass onto the non-interactive execution below.
                SessionUtil::setVar('interactive_init', true);

                return call_user_func($interactive_func);
            }
        }

        // non-interactive
        if (is_callable($func)) {
            if (call_user_func($func) != true) {
                return false;
            }
        }

        // Update state of module
        if (!$this->setState(array('id' => $args['id'], 'state' => ModUtil::STATE_ACTIVE))) {
            return LogUtil::registerError($this->__('Error! Could not change module state.'));
        }

        if (!System::isInstalling()) {
            // This should become an event handler - drak
            $category = ModUtil::getVar('Admin', 'defaultcategory');
            ModUtil::apiFunc('Admin', 'admin', 'addmodtocategory', array('module' => $modinfo['name'], 'category' => $category));
        }

        // All went ok so issue installed event
        $event = new Zikula_Event('installer.module.installed', null, $modinfo);
        $this->eventManager->notify($event);

        // Success
        return true;
    }

    /**
     * Upgrade a module.
     *
     * @param array $args All parameters passed to this function.
     *                      numeric $args['id']                  The module ID.
     *                      boolean $args['interactive_upgrade'] Whether or not to upgrade in interactive mode.
     *
     * @return boolean True on success, false on failure.
     */
    public function upgrade($args)
    {
        // Argument check
        if (!isset($args['id']) || !is_numeric($args['id'])) {
            return LogUtil::registerArgsError();
        }

        // Get module information
        $modinfo = ModUtil::getInfo($args['id']);
        if (empty($modinfo)) {
            return LogUtil::registerError($this->__('Error! No such module ID exists.'));
        }

        switch ($modinfo['state']) {
            case ModUtil::STATE_NOTALLOWED:
                return LogUtil::registerError($this->__f('Error! No permission to upgrade %s.', $modinfo['name']));
                break;
            default:
                if ($modinfo['state'] > 10) {
                    return LogUtil::registerError($this->__f('Error! %s is not compatible with this version of Zikula.', $modinfo['name']));
                }
        }

        $osdir = DataUtil::formatForOS($modinfo['directory']);
        ModUtil::dbInfoLoad($modinfo['name'], $osdir);
        $modpath = ($modinfo['type'] == ModUtil::TYPE_SYSTEM) ? 'system' : 'modules';

        // load module maintainence functions
        $oomod = ModUtil::isOO($modinfo['name']);

        if ($oomod) {
            ZLoader::addAutoloader($osdir, "$modpath/$osdir/lib");
        }

        $bootstrap = "$modpath/$osdir/bootstrap.php";
        if (file_exists($bootstrap)) {
            include_once $bootstrap;
        }

        if ($modinfo['type'] == ModUtil::TYPE_MODULE) {
            if (is_dir("modules/$osdir/locale")) {
                ZLanguage::bindModuleDomain($modinfo['name']);
            }
        }

        if (!$oomod && file_exists($file = "$modpath/$osdir/pninit.php")) {
            if (!include_once($file)) {
                LogUtil::registerError($this->__f("Error! Could not load a required file: '%s'.", $file));
            }
        }

        if ($oomod) {
            $className = ucwords($modinfo['name']) . '_Installer';
            $reflectionInstaller = new ReflectionClass($className);
            if (!$reflectionInstaller->isSubclassOf('Zikula_AbstractInstaller')) {
                LogUtil::registerError($this->__f("%s must be an instance of Zikula_AbstractInstaller", $className));
            }
            $installer = $reflectionInstaller->newInstanceArgs(array($this->serviceManager));
            $interactiveClass = ucwords($modinfo['name']) . '_Controller_InteractiveInstaller';
            $interactiveController = null;
            if (class_exists($interactiveClass)) {
                $reflectionInteractive = new ReflectionClass($interactiveClass);
                if (!$reflectionInteractive->isSubclassOf('Zikula_Controller_AbstractInteractiveInstaller')) {
                    LogUtil::registerError($this->__f("%s must be an instance of Zikula_Controller_AbstractInteractiveInstaller", $className));
                }
                $interactiveController = $reflectionInteractive->newInstance($this->serviceManager);
            }
        }

        // perform the actual upgrade of the module
        $func = ($oomod) ? array($installer, 'upgrade') : $modinfo['name'] . '_upgrade';
        $interactive_func = ($oomod) ? array($interactiveController, 'upgrade') : $modinfo['name'] . '_init_interactiveupgrade';

        // allow bypass of interactive upgrade during a new installation only.
        if (System::isInstalling() && is_callable($interactive_func) && !is_callable($func)) {
            return; // return void here
        }

        if (isset($args['interactive_upgrade']) && $args['interactive_upgrade'] == false && is_callable($interactive_func)) {
            if (is_array($interactive_func)) {
                // This must be an OO controller since callable is an array.
                // Because interactive installers extend the Zikula_AbstractController, is_callable will always return true because of the __call()
                // so we must check if the method actually exists by reflection - drak
                if ($reflectionInteractive->hasMethod('upgrade')) {
                    SessionUtil::setVar('interactive_upgrade', true);

                    return call_user_func($interactive_func, array('oldversion' => $modinfo['version']));
                }
            } else {
                // this is enclosed in the else so that if both conditions fail, execution will pass onto the non-interactive execution below.
                SessionUtil::setVar('interactive_upgrade', true);

                return call_user_func($interactive_func, array('oldversion' => $modinfo['version']));
            }
        }

        // non-interactive
        if (is_callable($func)) {
            $result = call_user_func($func, $modinfo['version']);
            if (is_string($result)) {
                if ($result != $modinfo['version']) {
                    // update the last successful updated version
                    $modinfo['version'] = $result;
                    $obj = DBUtil::updateObject($modinfo, 'modules', '', 'id', true);
                }

                return false;
            } elseif ($result != true) {
                return false;
            }
        }
        $modversion['version'] = '0';

        $modversion = Extensions_Util::getVersionMeta($osdir, $modpath);
        $version = $modversion['version'];

        // Update state of module
        $result = $this->setState(array('id' => $args['id'], 'state' => ModUtil::STATE_ACTIVE));
        if ($result) {
            LogUtil::registerStatus($this->__("Done! Module has been upgraded. Its status is now 'Active'."));
        } else {
            return false;
        }

        // Note the changes in the database...
        // Get module database info
        ModUtil::dbInfoLoad('Extensions');

        $obj = array('id'      => $args['id'],
                     'version' => $version);

        DBUtil::updateObject($obj, 'modules');

        // legacy to be removed from 1.4 - remove hooks during upgrade since we cannot rely on
        // module authors to do this - drak
        if ($oomod) {
            $tables = DBUtil::getTables();
            $hooksCol = $tables['hooks_column'];
            $where = "$hooksCol[smodule] = '$modinfo[name]' OR $hooksCol[tmodule] = '$modinfo[name]'";
            $hooks = DBUtil::selectObjectArray('hooks', $where);
            if ($hooks) {
                foreach ($hooks as $hook) {
                    DBUtil::deleteObject($hook, 'hooks');
                }
                LogUtil::registerStatus($this->__f("NOTICE! Legacy hook configurations for %s have been removed.", $modinfo['name']));
            }
        }

        // Upgrade succeeded, issue event.
        $event = new Zikula_Event('installer.module.upgraded', null, $modinfo);
        $this->eventManager->notify($event);

        // Success
        return true;
    }

    /**
     * Upgrade all modules.
     *
     * @return array An array of upgrade results, indexed by module name.
     */
    public function upgradeall()
    {
        $upgradeResults = array();
        $usersModule = array();

        // regenerate modules list
        $filemodules = $this->getfilemodules();
        $this->regenerate(array('filemodules' => $filemodules));

        // get a list of modules needing upgrading
        if ($this->listmodules(array('state' => ModUtil::STATE_UPGRADED))) {
            $newmods = $this->listmodules(array('state' => ModUtil::STATE_UPGRADED));

            // Sort upgrade order according to this list.
            $priorities = array('Extensions', 'Users' , 'Groups', 'Permissions', 'Admin', 'Blocks', 'Theme', 'Settings', 'Categories', 'SecurityCenter', 'Errors');
            $sortedList = array();
            foreach ($priorities as $priority) {
                foreach ($newmods as $key => $modinfo) {
                    if ($modinfo['name'] == $priority) {
                        $sortedList[] = $modinfo;
                        unset($newmods[$key]);
                    }
                }
            }

            $newmods = array_merge($sortedList, $newmods);

            foreach ($newmods as $mod) {
                $upgradeResults[$mod['name']] = $this->upgrade(array('id' => $mod['id']));
            }

            System::setVar('Version_Num', Zikula_Core::VERSION_NUM);
        }

        return $upgradeResults;
    }

    /**
     * Utility function to count the number of items held by this module.
     *
     * @param array $args All parameters passed to this function.
     *                      string  $args['letter'] Filter the count by the first letter of the module name; optional.
     *                      integer $args['state']  Filter the count by the module state; optional.
     *
     * @return integer The number of items held by this module.
     */
    public function countitems($args)
    {
        $dbtable = DBUtil::getTables();
        $modulescolumn = $dbtable['modules_column'];

        // filter my first letter of module
        if (isset($args['letter']) && !empty($args['letter'])) {
            $where[] = "$modulescolumn[name] LIKE '" . DataUtil::formatForStore($args['letter']) . "%'";
        }

        // filter by module state
        switch ($args['state']) {
            case ModUtil::STATE_UNINITIALISED:
            case ModUtil::STATE_INACTIVE:
            case ModUtil::STATE_ACTIVE:
            case ModUtil::STATE_MISSING:
            case ModUtil::STATE_UPGRADED:
            case ModUtil::STATE_INVALID:
                $where[] = "$modulescolumn[state] = '" . DataUtil::formatForStore($args['state']) . "'";
                break;
            default:
                if ($args['state'] > 10) {
                    $where[] = "$modulescolumn[state] > 10 ";
                }
        }

        // generate where clause
        $wheresql = '';
        if (isset($where) && is_array($where)) {
            $wheresql = 'WHERE ' . implode(' AND ', $where);
        }

        $count = DBUtil::selectObjectCount('modules', $wheresql);
        if ($count === false) {
            return LogUtil::registerError($this->__('Error! Could not load data.'));
        }

        return $count;
    }

    /**
     * Get available admin panel links.
     *
     * @return array An array of admin links.
     */
    public function getlinks()
    {
        $links = array();

        // assign variables from input
        $startnum = (int)FormUtil::getPassedValue('startnum', null, 'GET');
        $letter = FormUtil::getPassedValue('letter', null, 'GET');

        if (SecurityUtil::checkPermission('Extensions::', '::', ACCESS_ADMIN)) {
            $links[] = array('url' => ModUtil::url('Extensions', 'admin', 'view'),
                             'text' => $this->__('Modules list'),
                             'class' => 'z-icon-es-view',
                             'links' => array(
                                             array('url' => ModUtil::url('Extensions', 'admin', 'view'),
                                                   'text' => $this->__('All')),
                                             array('url' => ModUtil::url('Extensions', 'admin', 'view', array('state'=>ModUtil::STATE_UNINITIALISED)),
                                                   'text' => $this->__('Not installed')),
                                             array('url' => ModUtil::url('Extensions', 'admin', 'view', array('state'=>ModUtil::STATE_INACTIVE)),
                                                   'text' => $this->__('Inactive')),
                                             array('url' => ModUtil::url('Extensions', 'admin', 'view', array('state'=>ModUtil::STATE_ACTIVE)),
                                                   'text' => $this->__('Active')),
                                             array('url' => ModUtil::url('Extensions', 'admin', 'view', array('state'=>ModUtil::STATE_MISSING)),
                                                   'text' => $this->__('Files missing')),
                                             array('url' => ModUtil::url('Extensions', 'admin', 'view', array('state'=>ModUtil::STATE_UPGRADED)),
                                                   'text' => $this->__('New version uploaded')),
                                             array('url' => ModUtil::url('Extensions', 'admin', 'view', array('state'=>ModUtil::STATE_INVALID)),
                                                   'text' => $this->__('Invalid structure'))
                                               ));

            $links[] = array('url' => ModUtil::url('Extensions', 'admin', 'viewPlugins'),
                             'text' => $this->__('Plugins list'),
                             'class' => 'z-icon-es-gears',
                             'links' => array(
                                             array('url' => ModUtil::url('Extensions', 'admin', 'viewPlugins'),
                                                   'text' => $this->__('All')),
                                             array('url' => ModUtil::url('Extensions', 'admin', 'viewPlugins', array('state'=>PluginUtil::NOTINSTALLED)),
                                                   'text' => $this->__('Not installed')),
                                             array('url' => ModUtil::url('Extensions', 'admin', 'viewPlugins', array('state'=>PluginUtil::DISABLED)),
                                                   'text' => $this->__('Inactive')),
                                             array('url' => ModUtil::url('Extensions', 'admin', 'viewPlugins', array('state'=>PluginUtil::ENABLED)),
                                                   'text' => $this->__('Active'))
                                               ));

            $links[] = array('url' => ModUtil::url('Extensions', 'admin', 'viewPlugins', array('systemplugins' => true)),
                             'text' => $this->__('System Plugins'),
                             'class' => 'z-icon-es-gears',
                             'links' => array(
                                             array('url' => ModUtil::url('Extensions', 'admin', 'viewPlugins', array('systemplugins' => true)),
                                                   'text' => $this->__('All')),
                                             array('url' => ModUtil::url('Extensions', 'admin', 'viewPlugins', array('systemplugins' => true, 'state'=>PluginUtil::NOTINSTALLED)),
                                                   'text' => $this->__('Not installed')),
                                             array('url' => ModUtil::url('Extensions', 'admin', 'viewPlugins', array('systemplugins' => true, 'state'=>PluginUtil::DISABLED)),
                                                   'text' => $this->__('Inactive')),
                                             array('url' => ModUtil::url('Extensions', 'admin', 'viewPlugins', array('systemplugins' => true, 'state'=>PluginUtil::ENABLED)),
                                                   'text' => $this->__('Active'))
                                               ));

            $legacyHooks = DBUtil::selectObjectArray('hooks');
            if (System::isLegacyMode() && $legacyHooks) {
                $links[] = array('url' => ModUtil::url('Extensions', 'admin', 'legacyhooks', array('id' => 0)), 'text' => $this->__('Legacy hooks'), 'class' => 'z-icon-es-hook');
            }

            $links[] = array('url' => ModUtil::url('Extensions', 'admin', 'modifyconfig'), 'text' => $this->__('Settings'), 'class' => 'z-icon-es-config');
            //$filemodules = ModUtil::apiFunc('Extensions', 'admin', 'getfilemodules');
            //ModUtil::apiFunc('Extensions', 'admin', 'regenerate', array('filemodules' => $filemodules));

            // get a list of modules needing upgrading
            $newmods = ModUtil::apiFunc('Extensions', 'admin', 'listmodules', array('state' => ModUtil::STATE_UPGRADED));
            if ($newmods) {
                $links[] = array('url' => ModUtil::url('Extensions', 'admin', 'upgradeall'), 'text' => $this->__('Upgrade All'), 'class' => 'z-icon-es-config');
            }
        }

        return $links;
    }

    /**
     * Get all module dependencies.
     *
     * @param array $args All parameters sent to this function (not currently used).
     *
     * @return array Array of dependencies.
     */
    public function getdallependencies($args)
    {
        return DBUtil::selectObjectArray('module_deps', '', 'modid');
    }

    /**
     * Get dependencies for a module.
     *
     * @param array $args All parameters sent to this function.
     *                      numeric $args['modid'] Id of module to get dependencies for.
     *
     * @return array|boolean Array of dependencies; false otherwise.
     */
    public function getdependencies($args)
    {
        // Argument check
        if (!isset($args['modid']) || empty($args['modid']) || !is_numeric($args['modid'])) {
            return LogUtil::registerArgsError();
        }

        $where = "modid = '" . DataUtil::formatForStore($args['modid']) . "'";

        return DBUtil::selectObjectArray('module_deps', $where, 'modname');
    }

    /**
     * Get dependents of a module.
     *
     * @param array $args All parameters passed to this function.
     *                      numeric $args['modid'] Id of module to get dependents for.
     *
     * @return array|boolean Array of dependents; false otherwise.
     */
    public function getdependents($args)
    {
        // Argument check
        if (!isset($args['modid']) || empty($args['modid']) || !is_numeric($args['modid'])) {
            return LogUtil::registerArgsError();
        }

        $modinfo = ModUtil::getInfo($args['modid']);
        $where = "modname = '" . DataUtil::formatForStore($modinfo['name']) . "'";

        return DBUtil::selectObjectArray('module_deps', $where, 'modid');
    }

    /**
     * Check modules for consistency.
     *
     * @param array $args All parameters passed to this function.
     *                  array $args['filemodules'] Array of modules in the filesystem, as returned by {@link getfilemodules()}.
     *
     * @see    getfilemodules()
     *
     * @return array An array of arrays with links to inconsistencies.
     */
    public function checkconsistency($args)
    {
        // Security check
        if (!System::isInstalling()) {
            if (!SecurityUtil::checkPermission('Extensions::', '::', ACCESS_ADMIN)) {
                return LogUtil::registerPermissionError();
            }
        }

        // Argument check
        if (!isset($args['filemodules']) || !is_array($args['filemodules'])) {
            return LogUtil::registerArgsError();
        }

        $filemodules = $args['filemodules'];

        $modulenames = array();
        $displaynames = array();

        $errors_modulenames = array();
        $errors_displaynames = array();

        // check for duplicate names or display names
        foreach ($filemodules as $dir => $modinfo) {
            if (isset($modulenames[strtolower($modinfo['name'])])) {
                $errors_modulenames[] = array('name' => $modinfo['name'],
                        'dir1' => $modulenames[strtolower($modinfo['name'])],
                        'dir2' => $dir);
            }

            if (isset($displaynames[strtolower($modinfo['displayname'])])) {
                $errors_displaynames[] = array('name' => $modinfo['displayname'],
                        'dir1' => $displaynames[strtolower($modinfo['displayname'])],
                        'dir2' => $dir);
            }

            if (isset($displaynames[strtolower($modinfo['url'])])) {
                $errors_displaynames[] = array('name' => $modinfo['url'],
                        'dir1' => $displaynames[strtolower($modinfo['url'])],
                        'dir2' => $dir);
            }

            $modulenames[strtolower($modinfo['name'])] = $dir;
            $displaynames[strtolower($modinfo['displayname'])] = $dir;
        }

        // do we need to check for duplicate oldnames as well?
        return array('errors_modulenames'  => $errors_modulenames,
                     'errors_displaynames' => $errors_displaynames);
    }

    /**
     * Check if a module comes from the core.
     *
     * @param array $args All parameters sent to this function.
     *                      string $args['modulename'] The name of the module to check.
     *
     * @return boolean True if it's a core module; otherwise false.
     */
    public function iscoremodule($args)
    {
        static $coreModules;

        if (!isset($coreModules)) {
            $coreModules = array(
                'Admin',
                'Blocks',
                'Categories',
                'Errors',
                'Groups',
                'Mailer',
                'Extensions',
                'Permissions',
                'SecurityCenter',
                'Settings',
                'Theme',
                'Users',
            );
        }

        if (in_array($args['modulename'], $coreModules)) {
            return true;
        }

        return false;
    }

    // from here is to be moved out into legacy

    /**
     * Update module hook information.
     *
     * @param array $args All parameters passed to this function.
     *                      numeric $args['id'] The id number of the module to update.
     *
     * @deprected since 1.3.0
     *
     * @return boolean True on success, false on failure.
     */
    public function updatehooks($args)
    {
        // Argument check
        if (!isset($args['id']) || !is_numeric($args['id'])) {
            return LogUtil::registerArgsError();
        }
        // Security check
        if (!SecurityUtil::checkPermission('Extensions::', "::$args[id]", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Rename operation
        $dbtable = DBUtil::getTables();
        $hookscolumn = $dbtable['hooks_column'];

        // Hooks
        // Get module name
        $modinfo = ModUtil::getInfo($args['id']);

        // Delete hook regardless
        $where = "WHERE $hookscolumn[smodule] = '" . DataUtil::formatForStore($modinfo['name']) . "'
                    AND $hookscolumn[tmodule] <> ''";

        DBUtil::deleteWhere('hooks', $where);

        $where = "WHERE $hookscolumn[smodule] = ''";
        $orderBy = "ORDER BY $hookscolumn[tmodule], $hookscolumn[smodule] DESC";

        $objArray = DBUtil::selectObjectArray('hooks', $where, $orderBy);
        if ($objArray === false) {
            return false;
        }

        $ak = array_keys($objArray);
        foreach ($ak as $v) {
            // Get selected value of hook
            $hookvalue = FormUtil::getPassedValue('hooks_' . $objArray[$v]['tmodule']);
            // See if this is checked and isn't in the database
            if (isset($hookvalue) && empty($objArray[$v]['smodule'])) {
                $objArray[$v]['smodule'] = $modinfo['name'];
                if (DBUtil::insertObject($objArray[$v], 'hooks') === false) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get a list of modules calling a particular hook module
     *
     * @copyright (C) 2003 by the Xaraya Development Team.
     * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
     *
     * @param array $args All parameters passed to this function.
     *                      string  $args['hookmodname'] The hook module we're looking for.
     *                      numeric $args['hookobject']  The object of the hook (item, module, ...) (optional).
     *                      string  $args['hookaction']  The action on that object (transform, display, ...) (optional).
     *                      string  $args['hookarea']    The area we're dealing with (GUI, API) (optional).
     *
     * @deprecated since 1.3.0
     *
     * @return array|boolean An array of modules calling this hook module; false on error.
     */
    public function gethookedmodules($args)
    {
        // Argument check
        if (empty($args['hookmodname'])) {
            return LogUtil::registerArgsError();
        }

        $dbtable = DBUtil::getTables();
        $hookscolumn = $dbtable['hooks_column'];

        $where = "WHERE $hookscolumn[tmodule]='" . DataUtil::formatForStore($args['hookmodname']) . "'";
        if (!empty($args['hookobject'])) {
            $where .= " AND $hookscolumn[object]='" . DataUtil::formatForStore($args['hookobject']) . "'";
        }
        if (!empty($args['hookaction'])) {
            $where .= " AND $$hookscolumn[action]='" . DataUtil::formatForStore($args['hookaction']) . "'";
        }
        if (!empty($args['hookarea'])) {
            $where .= " AND $hookscolumn[tarea]='" . DataUtil::formatForStore($args['hookarea']) . "'";
        }

        $objArray = DBUtil::selectObjectArray('hooks', $where);

        // Check for an error with the database
        if ($objArray === false) {
            return false;
        }

        // modlist will hold the hooked modules
        static $modlist = array();
        foreach ($objArray as $obj) {
            $smod = $obj['smodule'];
            if (empty($smod)) {
                continue;
            }

            $styp = $obj['stype'];
            if (empty($styp)) {
                $styp = 0;
            }

            $modlist[$smod][$styp] = 1;
        }

        return $modlist;
    }

    /**
     * Enable hooks between a caller module and a hook module.
     *
     * @copyright (C) 2003 by the Xaraya Development Team.
     * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
     *
     * @param array $args All parameters passed to this function.
     *                      string $args['callermodname'] The name of the caller module.
     *                      string $args['hookmodname']   The name of the hook module.
     *
     * @deprecated since 1.3.0
     *
     * @return bool True if successful; otherwise false.
     */
    public function enablehooks($args)
    {
        // Argument check
        if (empty($args['callermodname']) || empty($args['hookmodname'])) {
            return LogUtil::registerArgsError();
        }

        $dbtable = DBUtil::getTables();
        $hookscolumn = $dbtable['hooks_column'];

        // Rename operation
        // Delete hooks regardless
        $where = "WHERE $hookscolumn[smodule] = '" . DataUtil::formatForStore($args['callermodname']) . "'
                    AND $hookscolumn[tmodule] = '" . DataUtil::formatForStore($args['hookmodname']) . "'";

        if (!DBUtil::deleteWhere('hooks', $where)) {
            return false;
        }

        $where = "WHERE $hookscolumn[smodule] = ''
                    AND $hookscolumn[tmodule] = '" . DataUtil::formatForStore($args['hookmodname']) . "'";

        $objArray = DBUtil::selectObjectArray('hooks', $where, '', -1, -1, 'id');
        if (!$objArray) {
            return false;
        }

        $newHooks = array();
        foreach ($objArray as $hook) {
            unset($hook['id']);
            $hook['smodule'] = $args['callermodname'];
            $newHooks[] = $hook;
        }

        $result = DBUtil::insertObjectArray($newHooks, 'hooks');
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * Disable hooks between a caller module and a hook module.
     *
     * @copyright (C) 2003 by the Xaraya Development Team.
     * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
     *
     * @param array $args All parameters passed to this function.
     *                      string $args['callermodname'] The name of the caller module.
     *                      string $args['hookmodname']   The name of the hook module.
     *
     * @deprecated since 1.3.0
     *
     * @return bool True if successful; otherwise false.
     */
    public function disablehooks($args)
    {
        // Argument check
        if (empty($args['callermodname']) || empty($args['hookmodname'])) {
            return LogUtil::registerArgsError();
        }
        if (empty($args['calleritemtype'])) {
            $args['calleritemtype'] = '';
        }

        // Rename operation
        $dbtable = DBUtil::getTables();
        $hookscolumn = $dbtable['hooks_column'];

        // Delete hooks regardless
        $where = "WHERE $hookscolumn[smodule] = '" . DataUtil::formatForStore($args['callermodname']) . "'
                    AND $hookscolumn[stype]   = '" . DataUtil::formatForStore($args['calleritemtype']) . "'
                    AND $hookscolumn[tmodule] = '" . DataUtil::formatForStore($args['hookmodname']) . "'";

        return DBUtil::deleteWhere('hooks', $where);
    }

    /**
     * Get a list of hooks for a given module.
     *
     * @param array $args All parameters sent to this function.
     *                      numeric $args['modid'] The modules id.
     *
     * @deprecated since 1.3.0
     *
     * @return array An array of hooks attached the module.
     */
    public function getmoduleshooks($args)
    {
        // Argument check
        if (!isset($args['modid']) || !is_numeric($args['modid'])) {
            return LogUtil::registerArgsError();
        }

        // check if module id is valid
        $modinfo = ModUtil::getInfo($args['modid']);
        if ($modinfo == false) {
            return LogUtil::registerError($this->__('Error! No such module ID exists.'));
        }

        $dbtable = DBUtil::getTables();
        $hookscolumn = $dbtable['hooks_column'];

        $where = "WHERE $hookscolumn[smodule] = ''
                     OR $hookscolumn[smodule] = '" . DataUtil::formatForStore($modinfo['name']) . "'";
        $orderBy = "ORDER BY $hookscolumn[tmodule], $hookscolumn[smodule] DESC";
        $objArray = DBUtil::selectObjectArray('hooks', $where, $orderBy);

        if ($objArray === false) {
            return false;
        }

        $displayed = array();
        $ak = array_keys($objArray);
        $myArray = array();
        foreach ($ak as $v) {
            if (isset($displayed[$objArray[$v]['tmodule']])) {
                continue;
            }
            $displayed[$objArray[$v]['tmodule']] = true;

            if (!empty($objArray[$v]['smodule'])) {
                $objArray[$v]['hookvalue'] = 1;
            } else {
                $objArray[$v]['hookvalue'] = 0;
            }
            array_push($myArray, $objArray[$v]);
        }

        return $myArray;
    }

    /**
     * Get a extended list of hooks for a given module.
     *
     * @param array $args All parameters sent to this function.
     *                      numeric $args['modid'] The module id.
     *
     * @deprecated since 1.3.0
     *
     * @return array An array of hooks attached the module.
     */
    public function getextendedmoduleshooks($args)
    {
        // Argument check
        if (!isset($args['modid']) || !is_numeric($args['modid'])) {
            return LogUtil::registerArgsError();
        }

        // check if module id is valid
        $modinfo = ModUtil::getInfo($args['modid']);
        if ($modinfo == false) {
            return LogUtil::registerError($this->__('Error! No such module ID exists.'));
        }

        $dbtable = DBUtil::getTables();
        $hookscolumn = $dbtable['hooks_column'];

        $where = "WHERE $hookscolumn[smodule] = ''";
        $orderBy = "ORDER BY $hookscolumn[action], $hookscolumn[sequence] ASC";
        $hooksArray = DBUtil::selectObjectArray('hooks', $where, $orderBy);

        // sort the hooks by action
        $grouped_hooks = array();
        foreach ($hooksArray as $hookobject) {
            if (!array_key_exists($hookobject['action'], $grouped_hooks)) {
                $grouped_hooks[$hookobject['action']] = array();
            }
            $hookobject['hookvalue'] = 0;
            $grouped_hooks[$hookobject['action']][$hookobject['tmodule']] = $hookobject;
        }
        if ($grouped_hooks === false) {
            return false;
        }

        $where = "WHERE $hookscolumn[smodule] = '" . DataUtil::formatForStore($modinfo['name']) . "'";
        $orderBy = "ORDER BY $hookscolumn[action], $hookscolumn[sequence] ASC";

        $objArray = DBUtil::selectObjectArray('hooks', $where, $orderBy);
        if ($objArray === false) {
            return false;
        }

        $displayed = array();
        $ak = array_keys($objArray);
        foreach ($ak as $v) {
            unset($grouped_hooks[$objArray[$v]['action']][$objArray[$v]['tmodule']]);
            $objArray[$v]['hookvalue'] = 1;
            $grouped_hooks[$objArray[$v]['action']][$objArray[$v]['tmodule']] = $objArray[$v];
        }

        return $grouped_hooks;
    }

    /**
     * Update module hook information, extended version.
     *
     * @param array $args All parameters passed to this function.
     *                      numeric $args['id'] The id number of the module to update.
     *
     * @deprecated since 1.3.0
     *
     * @return boolean True on success, false on failure.
     */
    public function extendedupdatehooks($args)
    {
        // Argument check
        if (!isset($args['id']) || !is_numeric($args['id'])) {
            return LogUtil::registerArgsError();
        }
        // Security check
        if (!SecurityUtil::checkPermission('Extensions::', "::$args[id]", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Rename operation
        $dbtable = DBUtil::getTables();

        $hookscolumn = $dbtable['hooks_column'];

        // Hooks
        // Get module information
        $modinfo = ModUtil::getInfo($args['id']);

        // Delete hook regardless
        $where = "WHERE $hookscolumn[smodule] = '" . DataUtil::formatForStore($modinfo['name']) . "'
                    AND $hookscolumn[tmodule] <> ''";

        DBUtil::deleteWhere('hooks', $where);

        $where = "WHERE $hookscolumn[smodule] = ''";
        $orderBy = "ORDER BY $hookscolumn[tmodule], $hookscolumn[smodule] DESC";

        // read the hooks themselves - the entries in the database that are not connected
        // with a module
        $objArray = DBUtil::selectObjectArray('hooks', $where, $orderBy);
        if ($objArray === false) {
            return false;
        }

        // sort the hooks by action
        $grouped_hooks = array();
        foreach ($objArray as $hookobject) {
            if (!array_key_exists($hookobject['action'], $grouped_hooks)) {
                $grouped_hooks[$hookobject['action']] = array();
            }
            $grouped_hooks[$hookobject['action']][$hookobject['tmodule']] = $hookobject;
        }

        // get hookvalues. This is an array of hookactions with each one
        // containing an array of hooks where the checkbox has been set
        // in short: hookvalues only contains the hooks the that the user
        // want s to activate for the selected module. As a side effect
        // the hooks are sorted :-)
        $hookvalues = FormUtil::getPassedValue('hooks');

        // cycle throught the hookvalues
        foreach ($hookvalues as $action => $actionarray) {
            // reset the sequence
            $sequence = 1;
            foreach ($actionarray as $smodule => $value) {
                $hookobject = $grouped_hooks[$action][$smodule];
                $hookobject['sequence'] = $sequence;
                $hookobject['smodule'] = $modinfo['name'];
                if (DBUtil::insertObject($hookobject, 'hooks') === false) {
                    return false;
                }
                $sequence++;
            }
        }

        return true;
    }
}
