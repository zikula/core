<?php
/**
 * Zikula Application Framework
 *
 * @version $Id$
 * @license GNU/GPLv2 (or at your option any later version).
 * Please see the NOTICE and LICENSE files distributed with this source
 */

class ModUtil
{
    /**
     * The pnModInitCoreVars preloads some module vars.
     *
     * Preloads module vars for a number of key modules to reduce sql statements.
     *
     * @return void
     */
    public static function initCoreVars()
    {
        global $pnmodvar;

        // don't init vars during the installer
        if (defined('_ZINSTALLVER')) {
            return;
        }

        // if we haven't got vars for this module yet then lets get them
        if (!isset($pnmodvar)) {
            $pnmodvar = array();
            $pntables = pnDBGetTables();
            $col = $pntables['module_vars_column'];

            $where = "$col[modname]='" . PN_CONFIG_MODULE .
                    "' OR $col[modname]='pnRender' OR $col[modname]='Theme' OR $col[modname]='Blocks' OR $col[modname]='Users' OR $col[modname]='Settings'";
            $profileModule = pnConfigGetVar('profilemodule', '');
            if (!empty($profileModule) && self::available($profileModule)) {
                $where .= " OR $col[modname] = '" . $profileModule . "'";
            }

            $pnmodvars = DBUtil::selectObjectArray('module_vars', $where);
            foreach ($pnmodvars as $var) {
                $pnmodvar[$var['modname']][$var['name']] = @unserialize($var['value']);
            }
        }
    }

    /**
     * Checks to see if a module variable is set.
     *
     * @param string $modname The name of the module.
     * @param string $name    The name of the variable.
     *
     * @return boolean True if the variable exists in the database, false if not.
     */
    public static function hasVar($modname, $name)
    {
        // define input, all numbers and booleans to strings
        $modname = isset($modname) ? ((string)$modname) : '';
        $name = isset($name) ? ((string)$name) : '';

        // make sure we have the necessary parameters
        if (!pnVarValidate($modname, 'mod') || !pnVarValidate($name, 'modvar')) {
            return false;
        }

        // get all module vars for this module
        $modvars = self::getVar($modname);

        return isset($modvars[$name]);
    }

    /**
     * The pnModGetVar function gets a module variable.
     *
     * If the name parameter is included then function returns the
     * module variable value.
     * if the name parameter is ommitted then function returns a multi
     * dimentional array of the keys and values for the module vars.
     *
     * @param string  $modname The name of the module.
     * @param string  $name    The name of the variable.
     * @param boolean $default The value to return if the requested modvar is not set.
     *
     * @return string|array If the name parameter is included then function returns
     *          string - module variable value
     *          if the name parameter is ommitted then function returns
     *          array - multi dimentional array of the keys
     *                  and values for the module vars.
     */
    public static function getVar($modname, $name = '', $default = false)
    {
        // if we don't know the modname then lets assume it is the current
        // active module
        if (!isset($modname)) {
            $modname = self::getName();
        }

        global $pnmodvar;

        // if we haven't got vars for this module yet then lets get them
        if (!isset($pnmodvar[$modname])) {
            $pntables = pnDBGetTables();
            $col = $pntables['module_vars_column'];
            $where = "WHERE $col[modname]='" . DataUtil::formatForStore($modname) . "'";
            $sort = ' '; // this is not a mistake, it disables the default sort for DBUtil::selectFieldArray()


            $results = DBUtil::selectFieldArray('module_vars', 'value', $where, $sort, false, 'name');
            foreach ($results as $k => $v) {
                // Be carefull to allow check for unserialize of empty values and boolean false
                $pnmodvar[$modname][$k] = @unserialize($v);
                if ($pnmodvar[$modname][$k] === false && $v != 'b:0;') {
                    // backwards compatibility for non-serialized vars
                    $pnmodvar[$modname][$k] = $v;
                }
            }
        }

        // if they didn't pass a variable name then return every variable
        // for the specified module as an associative array.
        // array('var1'=>value1, 'var2'=>value2)
        if (empty($name) && isset($pnmodvar[$modname])) {
            return $pnmodvar[$modname];
        }

        // since they passed a variable name then only return the value for
        // that variable
        if (isset($pnmodvar[$modname][$name])) {
            return $pnmodvar[$modname][$name];
        }

        // we don't know the required module var but we established all known
        // module vars for this module so the requested one can't exist.
        // we return the default (which itself defaults to false)
        return $default;
    }

    /**
     * The pnModSetVar Function sets a module variable.
     *
     * @param string $modname The name of the module.
     * @param string $name    The name of the variable.
     * @param string $value   The value of the variable.
     *
     * @return boolean True if successful, false otherwise.
     */
    public static function setVar($modname, $name, $value = '')
    {
        // define input, all numbers and booleans to strings
        $modname = isset($modname) ? ((string)$modname) : '';

        // validate
        if (!pnVarValidate($modname, 'mod') || !isset($name)) {
            return false;
        }

        global $pnmodvar;

        $obj = array();

        // TODO A [remove this check but first update pninit routines] drak
        if (defined('_ZINSTALLVER')) {
            $obj['value'] = DataUtil::is_serialized($value) ? $value : serialize($value);
        } else {
            $obj['value'] = serialize($value);
        }

        if (pnModVarExists($modname, $name)) {
            $pntable = pnDBGetTables();
            $cols = $pntable['module_vars_column'];
            $where = "WHERE $cols[modname] = '" . DataUtil::formatForStore($modname) . "'
                    AND $cols[name] = '" . DataUtil::formatForStore($name) . "'";
            $res = DBUtil::updateObject($obj, 'module_vars', $where);
        } else {
            $obj['name'] = $name;
            $obj['modname'] = $modname;
            $res = DBUtil::insertObject($obj, 'module_vars');
        }

        if ($res) {
            $pnmodvar[$modname][$name] = $value;
        }

        return (bool)$res;
    }

    /**
     * The pnModSetVars function sets multiple module variables.
     *
     * @param string $modname The name of the module.
     * @param array  $vars    An associative array of varnames/varvalues.
     *
     * @return boolean True if successful, false otherwise.
     */
    public static function setVars($modname, $vars)
    {
        $ok = true;
        foreach ($vars as $var => $value)
            $ok = $ok && self::setVar($modname, $var, $value);
        return $ok;
    }

    /**
     * The pnModDelVar function deletes a module variable.
     *
     * Delete a module variables. If the optional name parameter is not supplied all variables
     * for the module 'modname' are deleted.
     *
     * @param string $modname The name of the module.
     * @param string $name    The name of the variable (optional).
     *
     * @return boolean True if successful, false otherwise.
     */
    public static function delVar($modname, $name = '')
    {
        // define input, all numbers and booleans to strings
        $modname = isset($modname) ? ((string)$modname) : '';

        // validate
        if (!pnVarValidate($modname, 'modvar')) {
            return false;
        }

        global $pnmodvar;
        $val = null;
        if (empty($name)) {
            if (isset($pnmodvar[$modname])) {
                unset($pnmodvar[$modname]);
            }
        } else {
            if (isset($pnmodvar[$modname][$name])) {
                $val = $pnmodvar[$modname][$name];
                unset($pnmodvar[$modname][$name]);
            }
        }

        $pntable = pnDBGetTables();
        $cols = $pntable['module_vars_column'];

        // check if we're deleting one module var or all module vars
        $specificvar = '';
        $name = DataUtil::formatForStore($name);
        $modname = DataUtil::formatForStore($modname);
        if (!empty($name)) {
            $specificvar = " AND $cols[name] = '$name'";
        }

        $where = "WHERE $cols[modname] = '$modname' $specificvar";
        $res = (bool)DBUtil::deleteWhere('module_vars', $where);
        return ($val ? $val : $res);
    }

    /**
     * The ModUtil::getIdFromName function gets module ID given its name.
     *
     * @param string $module The name of the module.
     *
     * @return integer module ID.
     */
    public static function getIdFromName($module)
    {
        // define input, all numbers and booleans to strings
        $module = (isset($module) ? strtolower((string)$module) : '');

        // validate
        if (!pnVarValidate($module, 'mod')) {
            return false;
        }

        static $modid;

        if (!is_array($modid) || defined('_ZINSTALLVER')) {
            $modules = self::getModsTable();

            if ($modules === false) {
                return;
            }

            foreach ($modules as $mod) {
                $mName = strtolower($mod['name']);
                $modid[$mName] = $mod['id'];
                if (isset($mod['url']) && $mod['url']) {
                    $mdName = strtolower($mod['url']);
                    $modid[$mdName] = $mod['id'];
                }
            }

            if (!isset($modid[$module])) {
                $modid[$module] = false;
                return false;
            }
        }

        if (isset($modid[$module])) {
            return $modid[$module];
        }

        return false;
    }

    /**
     * The ModUtil::getInfo function gets information on module.
     *
     * Return array of module information or false if core ( id = 0 ).
     *
     * @param integer $modid The module ID.
     *
     * @return array|boolean Module information array or false.
     */
    public static function getInfo($modid = 0)
    {
        // a $modid of 0 is associated with the core ( pn_blocks.mid, ... ).
        if (!is_numeric($modid)) {
            return false;
        }

        if ($modid == 0) {
            // 0 = the core itself, create a basic dummy module
            $modinfo['name'] = 'zikula';
            $modinfo['id'] = 0;
            $modinfo['displayname'] = 'Zikula Core v' . System::VERSION_NUM;
            return $modinfo;
        }

        static $modinfo;

        if (!is_array($modinfo) || defined('_ZINSTALLVER')) {
            $modinfo = self::getModsTable();

            if (!$modinfo) {
                return null;
            }

            if (!isset($modinfo[$modid])) {
                $modinfo[$modid] = false;
                return $modinfo[$modid];
            }
        }

        if (isset($modinfo[$modid])) {
            return $modinfo[$modid];
        }

        return false;
    }

    /**
     * The pnModGetUserMods function gets a list of user modules.
     *
     * @return array An array of module information arrays.
     */
    public static function getUserMods()
    {
        return self::getTypeMods('user');
    }

    /**
     * The pnModGetProfilesMods function gets a list of profile modules.
     *
     * @return array An array of module information arrays.
     */
    public static function getProfileMods()
    {
        return self::getTypeMods('profile');
    }

    /**
     * The pnModGetMessageMods function gets a list of message modules.
     *
     * @return array An array of module information arrays.
     */
    public static function getMessageMods()
    {
        return self::getTypeMods('message');
    }

    /**
     * The pnModGetAdminMods function gets a list of administration modules.
     *
     * @return array An array of module information arrays.
     */
    public static function getAdminMods()
    {
        return self::getTypeMods('admin');
    }

    /**
     * The pnModGetTypeMods function gets a list of modules by module type.
     *
     * @param string $type The module type to get (either 'user' or 'admin') (optional) (default='user').
     *
     * @return array An array of module information arrays.
     */
    public static function getTypeMods($type = 'user')
    {
        if ($type != 'user' && $type != 'admin' && $type != 'profile' && $type != 'message')
            $type = 'user';

        static $modcache = array();

        if (!isset($modcache[$type]) || !$modcache[$type]) {
            $modcache[$type] = array();
            $cap = $type . '_capable';
            $mods = self::getAllMods();
            $ak = array_keys($mods);
            foreach ($ak as $k) {
                if ($mods[$k][$cap] == '1') {
                    $modcache[$type][] = $mods[$k];
                }
            }
        }

        return $modcache[$type];
    }

    /**
     * The pnModGetAllMods function gets a list of all modules.
     *
     * @return array An array of module information arrays.
     */
    public static function getAllMods()
    {
        static $modsarray = array();

        if (empty($modsarray)) {
            $pntable = pnDBGetTables();
            $modulescolumn = $pntable['modules_column'];
            $where = "WHERE $modulescolumn[state] = " . PNMODULE_STATE_ACTIVE . "
                  OR $modulescolumn[name] = 'Modules'";
            $orderBy = "ORDER BY $modulescolumn[displayname]";
            $modsarray = DBUtil::selectObjectArray('modules', $where, $orderBy);
            if ($modsarray === false) {
                return false;
            }
        }

        return $modsarray;
    }

    /**
     * Loads datbase definition for a module.
     *
     * @param string  $modname   The name of the module to load database definition for.
     * @param string  $directory Directory that module is in (if known).
     * @param boolean $force     Force table information to be reloaded.
     *
     * @return boolean True if successful, false otherwise.
     */
    public static function dbInfoLoad($modname, $directory = '', $force = false)
    {
        // define input, all numbers and booleans to strings
        $modname = (isset($modname) ? strtolower((string)$modname) : '');

        // default return value
        $data = false;

        // validate
        if (!pnVarValidate($modname, 'mod')) {
            return $data;
        }

        static $loaded = array();
        // Check to ensure we aren't doing this twice
        if (isset($loaded[$modname]) && !$force) {
            $result = true;
            return $result;
        }

        // Get the directory if we don't already have it
        if (empty($directory)) {
            // get the module info
            $modinfo = self::getInfo(self::getIdFromName($modname));
            $directory = $modinfo['directory'];
        }

        // Load the database definition if required
        $files = array();
        $files[] = "config/functions/$directory/pntables.php";
        $files[] = "system/$directory/pntables.php";
        $files[] = "modules/$directory/pntables.php";
        Loader::loadOneFile($files);

        $tablefunc = $modname . '_pntables';
        if (function_exists($tablefunc)) {
            $data = $tablefunc();
            $GLOBALS['pntables'] = array_merge((array)$GLOBALS['pntables'], (array)$data);
        }
        $loaded[$modname] = true;

        // V4B RNG: return data so we know which tables were loaded by this module
        return $data;
    }

    /**
     * Loads a module.
     *
     * @param string  $modname The name of the module.
     * @param string  $type    The type of functions to load.
     * @param boolean $force   Determines to load Module even if module isn't active.
     *
     * @return string|boolean Name of module loaded, or false on failure.
     */
    public static function load($modname, $type = 'user', $force = false)
    {
        if (strtolower(substr($type, -3)) == 'api') {
            return false;
        }
        return self::loadGeneric($modname, $type, $force);
    }

    /**
     * Load an API module.
     *
     * @param string  $modname The name of the module.
     * @param string  $type    The type of functions to load.
     * @param boolean $force   Determines to load Module even if module isn't active.
     *
     * @return string|boolean Name of module loaded, or false on failure.
     */
    public static function loadApi($modname, $type = 'user', $force = false)
    {
        return self::loadGeneric($modname, $type, $force, true);
    }

    /**
     * Load a module.
     *
     * @param string  $modname The name of the module.
     * @param string  $type    The type of functions to load.
     * @param boolean $force   Determines to load Module even if module isn't active.
     * @param boolean $api     Whether or not to load an API (or regular) module.
     *
     * @return string|boolean Name of module loaded, or false on failure.
     */
    public static function loadGeneric($modname, $type = 'user', $force = false, $api = false)
    {
        // define input, all numbers and booleans to strings
        $osapi = ($api ? 'api' : '');
        $modname = isset($modname) ? ((string)$modname) : '';
        $modtype = strtolower("$modname{$type}{$osapi}");

        static $loaded = array();
        if (!empty($loaded[$modtype])) {
            // Already loaded from somewhere else
            return true;
        }

        // check the modules state
        if (!$force && !self::available($modname) && $modname != 'Modules') {
            return false;
        }

        // get the module info
        $modinfo = self::getInfo(self::getIdFromName($modname));
        // check for bad pnVarValidate($modname)
        if (!$modinfo) {
            return false;
        }

        // create variables for the OS preped version of the directory
        $modpath = ($modinfo['type'] == 3) ? 'system' : 'modules';
        $osdirectory = DataUtil::formatForOS($modinfo['directory']);
        $ostype  = DataUtil::formatForOS($type);
        $cosfile = "config/functions/$osdirectory/pn{$ostype}{$osapi}.php";
        $mosfile = "$modpath/$osdirectory/pn{$ostype}{$osapi}.php";
        $mosdir  = "$modpath/$osdirectory/pn{$ostype}{$osapi}";
        $oopcontroller = "$modpath/$osdirectory/{$modname}_{$ostype}{$osapi}.php";
        $coopcontroller = "config/classes/$osdirectory/{$modname}_{$ostype}{$osapi}.php";

        if (file_exists($coopcontroller)) {
            // Load the file from modules
            Loader::includeOnce($coopcontroller);
        } elseif (file_exists($oopcontroller)) {
            // Load the file from modules
            Loader::includeOnce($oopcontroller);
        } elseif (file_exists($cosfile)) {
            // Load the file from config
            Loader::includeOnce($cosfile);
        } elseif (file_exists($mosfile)) {
            // Load the file from modules
            Loader::includeOnce($mosfile);
        } elseif (is_dir($mosdir)) {
        } else {
            // File does not exist
            return false;
        }
        $loaded[$modtype] = 1;

        // Load optional bootstrap
        $bootstrap = "$modpath/$osdirectory/bootstrap.php";
        if (file_exists($bootstrap)) {
            include_once $bootstrap;
        }

        if ($modinfo['i18n']) {
            ZLanguage::bindModuleDomain($modname);
        }

        // Load database info
        self::dbInfoLoad($modname, $modinfo['directory']);

        // add stylesheet to the page vars, this makes the modulestylesheet plugin obsolete,
        // but only for non-api loads as we would pollute the stylesheets
        // not during installation as the Theme engine may not be available yet and not for system themes
        // TO-DO: figure out how to determine if a userapi belongs to a hook module and load the
        //        corresponding css, perhaps with a new entry in modules table?
        if (!defined('_ZINSTALLVER')) {
            if ($api == false) {
                PageUtil::addVar('stylesheet', ThemeUtil::getModuleStylesheet($modname));
                if ($type == 'admin') {
                    // load special admin.css for administrator backend
                    PageUtil::addVar('stylesheet', ThemeUtil::getModuleStylesheet('Admin', 'admin.css'));
                }
            }
        }

        $event = new Event('module.postloadgeneric', null, array('modinfo' => $modinfo, 'type' => $type, 'force' => $force, 'api' => $api));
        EventManagerUtil::notify($event);

        return $modname;
    }

    /**
     * Run a module function.
     *
     * @param string $modname The name of the module.
     * @param string $type    The type of function to run.
     * @param string $func    The specific function to run.
     * @param array  $args    The arguments to pass to the function.
     *
     * @return mixed.
     */
    public static function func($modname, $type = 'user', $func = 'main', $args = array())
    {
        return pnModFuncExec($modname, $type, $func, $args);
    }

    /**
     * Run an module API function.
     *
     * @param string $modname The name of the module.
     * @param string $type    The type of function to run.
     * @param string $func    The specific function to run.
     * @param array  $args    The arguments to pass to the function.
     *
     * @return mixed.
     */
    public static function apiFunc($modname, $type = 'user', $func = 'main', $args = array())
    {
        if (empty($type)) {
            $type = 'user';
        } elseif (!pnVarValidate($type, 'api')) {
            return null;
        }

        if (empty($func)) {
            $func = 'main';
        }

        return self::exec($modname, $type, $func, $args, true);
    }

    /**
     * Run a module function.
     *
     * @param string  $modname The name of the module.
     * @param string  $type    The type of function to run.
     * @param string  $func    The specific function to run.
     * @param array   $args    The arguments to pass to the function.
     * @param boolean $api     Whether or not to execute an API (or regular) function.
     *
     * @return mixed.
     */
    public static function exec($modname, $type = 'user', $func = 'main', $args = array(), $api = false)
    {
        static $controllers;
        if (is_null($controllers)) {
            $controllers = array();
        }

        // define input, all numbers and booleans to strings
        $modname = isset($modname) ? ((string)$modname) : '';
        $ftype = ($api ? 'api' : '');
        $loadfunc = ($api ? 'ModUtil::loadApi' : 'pnModLoad');

        // validate
        if (!pnVarValidate($modname, 'mod')) {
            return null;
        }

        $modinfo = self::getInfo(self::getIDFromName($modname));
        $path = ($modinfo['type'] == '3' ? 'system' : 'modules');

        // Build function name and call function
        $modfunc = "{$modname}_{$type}{$ftype}_{$func}";
        $loaded = call_user_func_array($loadfunc, array($modname, $type));

        $className = "{$modname}_{$type}{$ftype}";
        $controller = null;

        if (class_exists($className)) {
            $r = new ReflectionClass($className);
            if (array_key_exists($className, $controllers)) {
                $controller = $controllers[$className];
                ZLoader::addAutoloader($modname, realpath(ZLOADER_PATH . "/$path"));
            } else {
                $controller = $r->newInstance();
                $controllers[$className] = $controller;
            }

            if (is_callable(array($controller, $func))) {
                $modfunc = array($controller, $func);
            }
        }

        $preExecuteEvent = new Event('module.preexecute', $controller, array('modfunc' => $modfunc, 'args' => $args, 'modinfo' => $modinfo, 'type' => $type, 'api' => $api));
        $postExecuteEvent = new Event('module.postexecute', $controller, array('modfunc' => $modfunc, 'args' => $args, 'modinfo' => $modinfo, 'type' => $type, 'api' => $api));
        if ($loaded) {
            if (is_callable($modfunc)) {
                EventManagerUtil::notify($preExecuteEvent);
                $postExecuteEvent->setData(call_user_func($modfunc, $args));
                return EventManagerUtil::notify($postExecuteEvent)->getData();
            }

            // get the theme
            if ($GLOBALS['loadstages'] & PN_CORE_THEME) {
                $theme = ThemeUtil::getInfo(ThemeUtil::getIDFromName(pnUserGetTheme()));
                if (file_exists($file = 'themes/' . $theme['directory'] . '/functions/' . $modname . "/pn{$type}{$ftype}/$func.php")) {
                    Loader::loadFile($file);
                    if (function_exists($modfunc)) {
                        EventManagerUtil::notify($preExecuteEvent)->getData();
                        $postExecuteEvent->setData(call_user_func($modfunc, $args));
                        return EventManagerUtil::notify($postExecuteEvent)->getData();
                    }
                }
            }

            if (file_exists($file = "config/functions/$modname/pn{$type}{$ftype}/$func.php")) {
                Loader::loadFile($file);
                if (is_callable($modfunc)) {
                    EventManagerUtil::notify($preExecuteEvent)->getData();
                    $postExecuteEvent->setData(call_user_func($modfunc, $args));
                    return EventManagerUtil::notify($postExecuteEvent)->getData();
                }
            }

            if (file_exists($file = "$path/$modname/pn{$type}{$ftype}/$func.php")) {
                Loader::loadFile($file);
                if (is_callable($modfunc)) {
                    return $modfunc($args);
                    EventManagerUtil::notify($preExecuteEvent)->getData();
                    $postExecuteEvent->setData(call_user_func($modfunc, $args));
                    return EventManagerUtil::notify($postExecuteEvent)->getData();
                }
            }

            // try to load plugin
            // This kind of eventhandler should
            // 1. Check $event['modfunc'] to see if it should run else exit silently.
            // 2. Do something like $result = {$event['modfunc']}({$event['args'});
            // 3. Save the result $event->setData($result).
            // 4. $event->setNotify().
            // return void
            $event = new Event('module.execute_not_found', null, array('modfunc' => $modfunc, 'args' => $args, 'modinfo' => $modinfo, 'type' => $type, 'api' => $api));
            EventManagerUtil::notifyUntil($event);
            if ($preExecuteEvent->hasNotified()) {
                return $preExecuteEvent->getData();
            }
        }
    }

    /**
     * Generate a module function URL.
     *
     * If the module is non-API compliant (type 1) then
     * a) $func is ignored.
     * b) $type=admin will generate admin.php?module=... and $type=user will generate index.php?name=...
     *
     * @param string       $modname      The name of the module.
     * @param string       $type         The type of function to run.
     * @param string       $func         The specific function to run.
     * @param array        $args         The array of arguments to put on the URL.
     * @param boolean|null $ssl          Set to constant null,true,false $ssl = true not $ssl = 'true'  null - leave the current status untouched,
     *                                   true - create a ssl url, false - create a non-ssl url.
     * @param string       $fragment     The framgment to target within the URL.
     * @param boolean|null $fqurl        Fully Qualified URL. True to get full URL, eg for Redirect, else gets root-relative path unless SSL.
     * @param boolean      $forcelongurl Force pnModURL to not create a short url even if the system is configured to do so.
     * @param boolean      $forcelang    Forcelang.
     *
     * @return sting Absolute URL for call
     */
    public static function url($modname, $type = 'user', $func = 'main', $args = array(), $ssl = null, $fragment = null, $fqurl = null, $forcelongurl = false, $forcelang=false)
    {
        // define input, all numbers and booleans to strings
        $modname = isset($modname) ? ((string)$modname) : '';

        // validate
        if (!pnVarValidate($modname, 'mod')) {
            return null;
        }

        //get the module info
        $modinfo = self::getInfo(self::getIDFromName($modname));

        // set the module name to the display name if this is present
        if (isset($modinfo['url']) && !empty($modinfo['url'])) {
            $modname = rawurlencode($modinfo['url']);
        }

        // define some statics as this API is likely to be called many times
        static $entrypoint, $host, $baseuri, $https, $shorturlstype, $shorturlsstripentrypoint, $shorturlsdefaultmodule;

        // entry point
        if (!isset($entrypoint)) {
            $entrypoint = pnConfigGetVar('entrypoint');
        }
        // Hostname
        if (!isset($host)) {
            $host = pnServerGetVar('HTTP_HOST');
        }
        if (empty($host))
            return false;
        // Base URI
        if (!isset($baseuri)) {
            $baseuri = pnGetBaseURI();
        }
        // HTTPS Support
        if (!isset($https)) {
            $https = pnServerGetVar('HTTPS');
        }
        // use friendly url setup
        if (!isset($shorturls)) {
            $shorturls = pnConfigGetVar('shorturls');
        }
        if (!isset($shorturlstype)) {
            $shorturlstype = pnConfigGetVar('shorturlstype');
        }
        if (!isset($shorturlsstripentrypoint)) {
            $shorturlsstripentrypoint = pnConfigGetVar('shorturlsstripentrypoint');
        }
        if (!isset($shorturlsdefaultmodule)) {
            $shorturlsdefaultmodule = pnConfigGetVar('shorturlsdefaultmodule');
        }
        if (isset($args['returnpage'])) {
            $shorturls = false;
        }

        $language = ($forcelang ? $forcelang : ZLanguage::getLanguageCode());

        // Only produce full URL when HTTPS is on or $ssl is set
        $siteRoot = '';
        if ((isset($https) && $https == 'on') || $ssl != null || $fqurl == true) {
            $protocol = 'http' . (($https == 'on' && $ssl !== false) || $ssl === true ? 's' : '');
            $secureDomain = pnConfigGetVar('secure_domain');
            $siteRoot = $protocol . '://' . (($secureDomain != '') ? $secureDomain : ($host . $baseuri)) . '/';
        }

        // Only convert User URLs. Exclude links that append a theme parameter
        if ($shorturls && $shorturlstype == 0 && $type == 'user' && $forcelongurl == false) {
            if (isset($args['theme'])) {
                $theme = $args['theme'];
                unset($args['theme']);
            }
            // Module-specific Short URLs
            $url = ModUtil::apiFunc($modinfo['name'], 'user', 'encodeurl', array('modname' => $modname, 'type' => $type, 'func' => $func, 'args' => $args));
            if (empty($url)) {
                // depending on the settings, we have generic directory based short URLs:
                // [language]/[module]/[function]/[param1]/[value1]/[param2]/[value2]
                // [module]/[function]/[param1]/[value1]/[param2]/[value2]
                $vars = '';
                foreach ($args as $k => $v) {
                    if (is_array($v)) {
                        foreach ($v as $k2 => $w) {
                            if (is_numeric($w) || !empty($w)) {
                                // we suppress '', but allow 0 as value (see #193)
                                $vars .= '/' . $k . '[' . $k2 . ']/' . $w; // &$k[$k2]=$w
                            }
                        }
                    } elseif (is_numeric($v) || !empty($v)) {
                        // we suppress '', but allow 0 as value (see #193)
                        $vars .= "/$k/$v"; // &$k=$v
                    }
                }
                $vars = substr($vars, 1);
                if ((!empty($func) && $func != 'main') || $vars != '') {
                    $func = "/$func/";
                } else {
                    $func = '/';
                }
                $url = $modname . $func . $vars;
            }

            if ($shorturlsdefaultmodule == $modinfo['name'] && $url != "{$modinfo['url']}/") {
                $url = str_replace("{$modinfo['url']}/", '', $url);
            }
            if (isset($theme)) {
                $url = rawurlencode($theme) . '/' . $url;
            }

            // add language param to short url
            if (ZLanguage::isRequiredLangParam() || $forcelang) {
                $url = "$language/" . $url;
            }
            if (!$shorturlsstripentrypoint) {
                $url = "$entrypoint/$url" . (!empty($query) ? '?' . $query : '');
            } else {
                $url = "$url" . (!empty($query) ? '?' . $query : '');
            }

        } else {
            // Regular URLs

            // The arguments
            if ($modinfo['type'] == 1) {
                if ($type == 'admin') {
                    $urlargs[] = "name=$modname";
                    $entrypoint = 'admin.php';
                } else {
                    $urlargs[] = "name=$modname";
                }
            } else {
                $urlargs = "module=$modname";
                if ((!empty($type)) && ($type != 'user')) {
                    $urlargs .= "&type=$type";
                }

                if ((!empty($func)) && ($func != 'main')) {
                    $urlargs .= "&func=$func";
                }
            }

            // add lang param to URL
            if (ZLanguage::isRequiredLangParam() || $forcelang) {
                $urlargs .= "&lang=$language";
            }

            $url = "$entrypoint?$urlargs";

            // <rabbitt> added array check on args
            // April 11, 2003
            if (!is_array($args)) {
                return false;
            } else {
                foreach ($args as $k => $v) {
                    if (is_array($v)) {
                        foreach ($v as $l => $w) {
                            if (is_numeric($w) || !empty($w)) {
                                // we suppress '', but allow 0 as value (see #193)
                                $url .= "&$k" . "[$l]=$w";
                            }
                        }
                    } elseif (is_numeric($v) || !empty($v)) {
                        // we suppress '', but allow 0 as value (see #193)
                        $url .= "&$k=$v";
                    }
                }
            }
        }

        if (isset($fragment)) {
            $url .= '#' . $fragment;
        }

        return $siteRoot . $url;
    }

    /**
     * Check if a module is available.
     *
     * @param string  $modname The name of the module.
     * @param boolean $force   Force.
     *
     * @return boolean True if the module is available, false if not.
     */
    public static function available($modname = null, $force = false)
    {
        // define input, all numbers and booleans to strings
        $modname = (isset($modname) ? strtolower((string)$modname) : '');

        // validate
        if (!pnVarValidate($modname, 'mod')) {
            return false;
        }

        static $modstate = array();

        if (!isset($modstate[$modname]) || $force == true) {
            $modinfo = self::getInfo(self::getIDFromName($modname));
            $modstate[$modname] = $modinfo['state'];
        }

        if ((isset($modstate[$modname]) &&
                        $modstate[$modname] == PNMODULE_STATE_ACTIVE) || (preg_match('/(modules|admin|theme|block|groups|permissions|users)/i', $modname) &&
                        (isset($modstate[$modname]) && ($modstate[$modname] == PNMODULE_STATE_UPGRADED || $modstate[$modname] == PNMODULE_STATE_INACTIVE)))) {
            return true;
        }

        return false;
    }

    /**
     * Get name of current top-level module.
     *
     * @return string The name of the current top-level module, false if not in a module.
     */
    public static function getName()
    {
        static $module;

        if (!isset($module)) {
            $type = FormUtil::getPassedValue('type', null, 'GETPOST');
            $module = FormUtil::getPassedValue('module', null, 'GETPOST');

            if (empty($module)) {
                $module = pnConfigGetVar('startpage');
            }

            // the parameters may provide the module alias so lets get
            // the real name from the db
            $modinfo = self::getInfo(self::getIdFromName($module));
            if (isset($modinfo['name'])) {
                $module = $modinfo['name'];
                if ($type != 'init' && !ModUtil::available($module)) {
                    // anything from user.php is the user module
                    // not really - of course but it'll do..... [markwest]
                    if (stristr($_SERVER['PHP_SELF'], 'user.php')) {
                        $module = 'Users';
                    } else {
                        $module = pnConfigGetVar('startpage');
                    }
                }
            }
        }

        return $module;
    }

    /**
     * Register a hook function.
     *
     * @param object $hookobject The hook object.
     * @param string $hookaction The hook action.
     * @param string $hookarea   The area of the hook (either 'GUI' or 'API').
     * @param string $hookmodule Name of the hook module.
     * @param string $hooktype   Name of the hook type.
     * @param string $hookfunc   Name of the hook function.
     *
     * @return boolean True if successful, false otherwise.
     */
    public static function registerHook($hookobject, $hookaction, $hookarea, $hookmodule, $hooktype, $hookfunc)
    {
        // define input, all numbers and booleans to strings
        $hookmodule = isset($hookmodule) ? ((string)$hookmodule) : '';

        // validate
        if (!pnVarValidate($hookmodule, 'mod')) {
            return false;
        }

        // Insert hook
        $obj = array('object' => $hookobject, 'action' => $hookaction, 'tarea' => $hookarea, 'tmodule' => $hookmodule, 'ttype' => $hooktype, 'tfunc' => $hookfunc);
        return (bool)DBUtil::insertObject($obj, 'hooks', 'id');
    }

    /**
     * Unregister a hook function.
     *
     * @param string $hookobject The hook object.
     * @param string $hookaction The hook action.
     * @param string $hookarea   The area of the hook (either 'GUI' or 'API').
     * @param string $hookmodule Name of the hook module.
     * @param string $hooktype   Name of the hook type.
     * @param string $hookfunc   Name of the hook function.
     *
     * @return boolean True if successful, false otherwise.
     */
    public static function unregisterHook($hookobject, $hookaction, $hookarea, $hookmodule, $hooktype, $hookfunc)
    {
        // define input, all numbers and booleans to strings
        $hookmodule = isset($hookmodule) ? ((string)$hookmodule) : '';

        // validate
        if (!pnVarValidate($hookmodule, 'mod')) {
            return false;
        }

        // Get database info
        $pntable = pnDBGetTables();
        $hookscolumn = $pntable['hooks_column'];
        // Remove hook
        $where = "WHERE $hookscolumn[object] = '" . DataUtil::formatForStore($hookobject) . "'
              AND $hookscolumn[action] = '" . DataUtil::formatForStore($hookaction) . "'
              AND $hookscolumn[tarea] = '" . DataUtil::formatForStore($hookarea) . "'
              AND $hookscolumn[tmodule] = '" . DataUtil::formatForStore($hookmodule) . "'
              AND $hookscolumn[ttype] = '" . DataUtil::formatForStore($hooktype) . "'
              AND $hookscolumn[tfunc] = '" . DataUtil::formatForStore($hookfunc) . "'";

        return (bool)DBUtil::deleteWhere('hooks', $where);
    }

    /**
     * Carry out hook operations for module.
     *
     * @param string  $hookobject The object the hook is called for - one of 'item', 'category' or 'module'.
     * @param string  $hookaction The action the hook is called for - one of 'new', 'create', 'modify', 'update', 'delete', 'transform', 'display', 'modifyconfig', 'updateconfig'.
     * @param integer $hookid     The id of the object the hook is called for (module-specific).
     * @param array   $extrainfo  Extra information for the hook, dependent on hookaction.
     * @param boolean $implode    Implode collapses all display hooks into a single string - default to true for compatability with .7x.
     *
     * @return string|array String output from GUI hooks, extrainfo array for API hooks.
     */
    public static function callHooks($hookobject, $hookaction, $hookid, $extrainfo = array(), $implode = true)
    {
        static $modulehooks;

        if (!isset($hookaction)) {
            return null;
        }

        if (isset($extrainfo['module']) && (self::available($extrainfo['module']) || strtolower($hookobject) == 'module' || strtolower($extrainfo['module']) == 'zikula')) {
            $modname = $extrainfo['module'];
        } else {
            $modname = pnModGetName();
        }

        $lModname = strtolower($modname);
        if (!isset($modulehooks[$lModname])) {
            // Get database info
            $pntable = pnDBGetTables();
            $hookscolumn = $pntable['hooks_column'];
            $where = "WHERE $hookscolumn[smodule] = '" . DataUtil::formatForStore($modname) . "'";
            $orderby = "$hookscolumn[sequence] ASC";
            $hooks = DBUtil::selectObjectArray('hooks', $where, $orderby);
            $modulehooks[$lModname] = $hooks;
        }

        $gui = false;
        $output = array();
        // Call each hook
        foreach ($modulehooks[$lModname] as $modulehook) {
            if (!isset($extrainfo['tmodule']) || (isset($extrainfo['tmodule']) && $extrainfo['tmodule'] == $modulehook['tmodule'])) {
                if (($modulehook['action'] == $hookaction) && ($modulehook['object'] == $hookobject)) {
                    if (isset($modulehook['tarea']) && $modulehook['tarea'] == 'GUI') {
                        $gui = true;
                        if (self::available($modulehook['tmodule'], $modulehook['ttype']) && self::load($modulehook['tmodule'], $modulehook['ttype'])) {
                            $output[$modulehook['tmodule']] = pnModFunc($modulehook['tmodule'],
                                    $modulehook['ttype'],
                                    $modulehook['tfunc'],
                                    array('objectid' => $hookid, 'extrainfo' => $extrainfo));
                        }
                    } else {
                        if (isset($modulehook['tmodule']) &&
                                self::available($modulehook['tmodule'], $modulehook['ttype']) &&
                                self::loadApi($modulehook['tmodule'], $modulehook['ttype'])) {
                            $extrainfo = ModUtil::apiFunc($modulehook['tmodule'], $modulehook['ttype'], $modulehook['tfunc'], array('objectid' => $hookid, 'extrainfo' => $extrainfo));
                        }
                    }
                }
            }
        }

        // check what type of information we need to return
        $hookaction = strtolower($hookaction);
        if ($gui || $hookaction == 'display' || $hookaction == 'new' || $hookaction == 'modify' || $hookaction == 'modifyconfig') {
            if ($implode || empty($output)) {
                $output = implode("\n", $output);
            }

            // This event expects that you might modify the $event['output'].  Check array_key_exists('output', $event) in event handler.
            $event = new Event('module.postcallhooks', null, array(
                            'gui' => $gui,
                            'hookobject' => $hookobject,
                            'hookaction' => $hookaction,
                            'hookid' => $hookid,
                            'extrainfo' => $extrainfo,
                            'implode' => $implode,
                            'output' => $output));
            EventManagerUtil::notify($event);
            return $event['output'];
        }

        // Check array_key_exists('output', $event) in event handler to distinguish from above hook where you might modify $event['output'].
        $event = new Event('module.postcallhooks', null, array(
                        'gui' => $gui,
                        'hookobject' => $hookobject,
                        'hookaction' => $hookaction,
                        'hookid' => $hookid,
                        'extrainfo' => $extrainfo,
                        'implode' => $implode));
        EventManagerUtil::notify($event);
        return $event['extrainfo'];
    }

    /**
     * Determine if a module is hooked by another module.
     *
     * @param string $tmodule The target module.
     * @param string $smodule The source module - default the current top most module.
     *
     * @return boolean True if the current module is hooked by the target module, false otherwise.
     */
    public static function isHooked($tmodule, $smodule)
    {
        static $hooked = array();

        if (isset($hooked[$tmodule][$smodule])) {
            return $hooked[$tmodule][$smodule];
        }

        // define input, all numbers and booleans to strings
        $tmodule = isset($tmodule) ? ((string)$tmodule) : '';
        $smodule = isset($smodule) ? ((string)$smodule) : '';

        // validate
        if (!pnVarValidate($tmodule, 'mod') || !pnVarValidate($smodule, 'mod')) {
            return false;
        }

        // Get database info
        $pntable = pnDBGetTables();
        $hookscolumn = $pntable['hooks_column'];

        // Get applicable hooks
        $where = "WHERE $hookscolumn[smodule] = '" . DataUtil::formatForStore($smodule) . "'
              AND $hookscolumn[tmodule] = '" . DataUtil::formatForStore($tmodule) . "'";

        $hooked[$tmodule][$smodule] = $numitems = DBUtil::selectObjectCount('hooks', $where);
        $hooked[$tmodule][$smodule] = ($numitems > 0);

        return $hooked[$tmodule][$smodule];
    }

    /**
     * Get the base directory for a module.
     *
     * Example: If the webroot is located at
     * /var/www/html
     * and the module name is Template and is found
     * in the modules directory then this function
     * would return /var/www/html/modules/Template
     *
     * If the Template module was located in the system
     * directory then this function would return
     * /var/www/html/system/Template
     *
     * This allows you to say:
     * include(pnModGetBaseDir() . '/includes/private_functions.php');.
     *
     * @param string $modname Name of module to that you want the base directory of.
     *
     * @return string The path from the root directory to the specified module.
     */
    public static function pnModGetBaseDir($modname = '')
    {
        if (empty($modname)) {
            $modname = self::getName();
        }

        $path = pnGetBaseURI();
        $directory = 'system/' . $modname;
        if ($path != '') {
            $path .= '/';
        }

        $url = $path . $directory;
        if (!is_dir($url)) {
            $directory = 'modules/' . $modname;
            $url = $path . $directory;
        }

        return $url;
    }

    /**
     * Gets the modules table.
     *
     * Small wrapper function to avoid duplicate sql.
     *
     * @return array An array modules table.
     */
    public static function getModsTable()
    {
        static $modstable;

        if (!isset($modstable) || defined('_ZINSTALLVER')) {
            $modstable = DBUtil::selectObjectArray('modules', '', '', -1, -1, 'id');
            foreach ($modstable as $mid => $module) {
                $modstable[$mid]['i18n'] = ($module['type'] == 2 ? true : false);
                if (!isset($module['url']) || empty($module['url'])) {
                    $modstable[$mid]['url'] = $module['displayname'];
                }
            }
        }

        return $modstable;
    }

    /**
     * Generic modules select function. Only modules in the module
     * table are returned which means that new/unscanned modules
     * will not be returned
     *
     * @param where The where clause to use for the select
     * @param sort  The sort to use
     *
     * @return The resulting module object array
     */
    public static function getModules($where='', $sort='displayname')
    {
        return DBUtil::selectObjectArray('modules', $where, $sort);
    }


    /**
     * Return an array of modules in the specified state, only modules in
     * the module table are returned which means that new/unscanned modules
     * will not be returned
     *
     * @param state    The module state (optional) (defaults = active state)
     * @param sort  The sort to use
     *
     * @return The resulting module object array
     */
    public static function getModulesByState($state=3, $sort='displayname')
    {
        $pntables     = pnDBGetTables();
        $moduletable  = $pntables['modules'];
        $modulecolumn = $pntables['modules_column'];

        $where = "$modulecolumn[state] = $state";
        return DBUtil::selectObjectArray ('modules', $where, $sort);
    }

}