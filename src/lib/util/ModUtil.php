<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Util
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Module Util.
 */
class ModUtil
{
    // States
    const STATE_UNINITIALISED = 1;
    const STATE_INACTIVE = 2;
    const STATE_ACTIVE = 3;
    const STATE_MISSING = 4;
    const STATE_UPGRADED = 5;
    const STATE_NOTALLOWED = 6;
    const STATE_INVALID = -1;

    const CONFIG_MODULE = 'ZConfig';

    // Types
    const TYPE_MODULE = 2;
    const TYPE_SYSTEM = 3;
    const TYPE_CORE = 4;

    // Module dependency states
    const DEPENDENCY_REQUIRED = 1;
    const DEPENDENCY_RECOMMENDED = 2;
    const DEPENDENCY_CONFLICTS = 3;

    /**
     * Memory of object oriented modules.
     *
     * @var array
     */
    protected static $ooModules = array();
    /**
     * Module info cache.
     *
     * @var array
     */
    protected static $modinfo;
    /**
     * Module vars.
     *
     * @var ArrayObject
     */
    protected static $modvars = array();

    /**
     * Internal module cache.
     *
     * @var array
     */
    protected static $cache = array();

    /**
     * Module variables getter.
     *
     * @return ArrayObject
     */
    public static function getModvars()
    {
        return self::$modvars;
    }

    /**
     * Flush this static class' cache.
     *
     * @return void
     */
    public static function flushCache()
    {
        self::$cache = array();
    }

    /**
     * The initCoreVars preloads some module vars.
     *
     * Preloads module vars for a number of key modules to reduce sql statements.
     *
     * @return void
     */
    public static function initCoreVars($force=false)
    {
        // The empty arrays for handlers and settings are required to prevent messages with E_ALL error reporting
        self::$modvars = new ArrayObject(array(
                EventUtil::HANDLERS => array(),
                ServiceUtil::HANDLERS => array(),
                'Settings'          => array(),
        ));

        // don't init vars during the installer or upgrader
        if (!$force && System::isInstalling()) {
            return;
        }

        // This loads all module variables into the modvars static class variable.
        $modvars = DBUtil::selectObjectArray('module_vars');
        foreach ($modvars as $var) {
            if (!array_key_exists($var['modname'], self::$modvars)) {
                self::$modvars[$var['modname']] = array();
            }
            if (array_key_exists($var['name'], $GLOBALS['ZConfig']['System'])) {
                self::$modvars[$var['modname']][$var['name']] = $GLOBALS['ZConfig']['System'][$var['name']];
            } elseif ($var['value'] == '0' || $var['value'] == '1') {
                self::$modvars[$var['modname']][$var['name']] = $var['value'];
            } else {
                self::$modvars[$var['modname']][$var['name']] = unserialize($var['value']);
            }
         }

         // Pre-load the module variables array with empty arrays for known modules that
         // do not define any module variables to prevent unnecessary SQL queries to
         // the module_vars table.
         $knownModules = self::getAllMods();
         foreach ($knownModules as $key => $mod) {
             if (!array_key_exists($mod['name'], self::$modvars)) {
                 self::$modvars[$mod['name']] = array();
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
        if (!System::varValidate($modname, 'mod') || !System::varValidate($name, 'modvar')) {
            return false;
        }

        // The cast to (array) is for the odd instance where self::$modvars[$modname] is set to null--not sure if this is really needed.
        $varExists = isset(self::$modvars[$modname]) && array_key_exists($name, (array)self::$modvars[$modname]);

        if (!$varExists && System::isUpgrading()) {
            // Handle the upgrade edge case--the call to getVar() ensures vars for the module are loaded if newly available.
            $modvars = self::getVar($modname);
            $varExists = array_key_exists($name, (array)$modvars);
        }

        return $varExists;
    }

    /**
     * The getVar method gets a module variable.
     *
     * If the name parameter is included then method returns the
     * module variable value.
     * if the name parameter is ommitted then method returns a multi
     * dimentional array of the keys and values for the module vars.
     *
     * @param string  $modname The name of the module or pseudo-module (e.g., 'Users', 'ZConfig', '/EventHandlers').
     * @param string  $name    The name of the variable.
     * @param boolean $default The value to return if the requested modvar is not set.
     *
     * @return string|array If the name parameter is included then method returns
     *          string - module variable value
     *          if the name parameter is ommitted then method returns
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

        // if we haven't got vars for this module (or pseudo-module) yet then lets get them
        if (!array_key_exists($modname, self::$modvars)) {
            // A query out to the database should only be needed if the system is upgrading. Use the installing flag to determine this.
            if (System::isUpgrading()) {
                $tables = DBUtil::getTables();
                $col = $tables['module_vars_column'];
                $where = "WHERE $col[modname] = '" . DataUtil::formatForStore($modname) . "'";
                // The following line is not a mistake. A sort string containing one space is used to disable the default sort for DBUtil::selectFieldArray().
                $sort = ' ';

                $results = DBUtil::selectFieldArray('module_vars', 'value', $where, $sort, false, 'name');

                if (is_array($results)) {
                    if (!empty($results)) {
                        foreach ($results as $k => $v) {
                            // ref #2045 vars are being stored with 0/1 unserialised.
                            if (array_key_exists($k, $GLOBALS['ZConfig']['System'])) {
                                self::$modvars[$modname][$k] = $GLOBALS['ZConfig']['System'][$k];
                            } elseif ($v == '0' || $v == '1') {
                                self::$modvars[$modname][$k] = $v;
                            } else {
                                self::$modvars[$modname][$k] = unserialize($v);
                            }
                        }
                    }
                }
                // TODO - There should probably be an exception thrown here if $results === false
            } else {
                // Prevent a re-query for the same module in the future, where the module does not define any module variables.
                self::$modvars[$modname] = array();
            }
        }

        // if they didn't pass a variable name then return every variable
        // for the specified module as an associative array.
        // array('var1' => value1, 'var2' => value2)
        if (empty($name) && array_key_exists($modname, self::$modvars)) {
            return self::$modvars[$modname];
        }

        // since they passed a variable name then only return the value for
        // that variable
        if (isset(self::$modvars[$modname]) && array_key_exists($name, self::$modvars[$modname])) {
            return self::$modvars[$modname][$name];
        }

        // we don't know the required module var but we established all known
        // module vars for this module so the requested one can't exist.
        // we return the default (which itself defaults to false)
        return $default;
    }

    /**
     * The setVar method sets a module variable.
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
        if (!System::varValidate($modname, 'mod') || !isset($name)) {
            return false;
        }

        $obj = array();
        $obj['value'] = serialize($value);

        if (self::hasVar($modname, $name)) {
            $tables = DBUtil::getTables();
            $cols = $tables['module_vars_column'];
            $where = "WHERE $cols[modname] = '" . DataUtil::formatForStore($modname) . "'
                         AND $cols[name] = '" . DataUtil::formatForStore($name) . "'";
            $res = DBUtil::updateObject($obj, 'module_vars', $where);
        } else {
            $obj['name'] = $name;
            $obj['modname'] = $modname;
            $res = DBUtil::insertObject($obj, 'module_vars');
        }

        if ($res) {
            self::$modvars[$modname][$name] = $value;
        }

        return (bool)$res;
    }

    /**
     * The setVars method sets multiple module variables.
     *
     * @param string $modname The name of the module.
     * @param array  $vars    An associative array of varnames/varvalues.
     *
     * @return boolean True if successful, false otherwise.
     */
    public static function setVars($modname, array $vars)
    {
        $ok = true;
        foreach ($vars as $var => $value) {
            $ok = $ok && self::setVar($modname, $var, $value);
        }

        return $ok;
    }

    /**
     * The delVar method deletes a module variable.
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
        if (!System::varValidate($modname, 'modvar')) {
            return false;
        }

        $val = null;
        if (!isset(self::$modvars[$modname])) {
            return $val;
        }
        if (empty($name)) {
            if (array_key_exists($modname, self::$modvars)) {
                unset(self::$modvars[$modname]);
            }
        } else {
            if (array_key_exists($name, self::$modvars[$modname])) {
                $val = self::$modvars[$modname][$name];

                // we're dealing with an ArrayObject, so we cannot unset() deep keys.
                $array = self::$modvars[$modname];
                unset($array[$name]);
                self::$modvars[$modname] = $array;
            }
        }

        $tables = DBUtil::getTables();
        $cols = $tables['module_vars_column'];

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
     * Get Module meta info.
     *
     * @param string $module Module name.
     *
     * @return array|boolean Module information array or false.
     */
    public static function getInfoFromName($module)
    {
        return self::getInfo(self::getIdFromName($module));
    }

    /**
     * The getIdFromName method gets module ID given its name.
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
        if (!System::varValidate($module, 'mod')) {
            return false;
        }

        if (!isset(self::$cache['modid'])) {
            self::$cache['modid'] = null;
        }

        if (!is_array(self::$cache['modid']) || System::isInstalling()) {
            $modules = self::getModsTable();

            if ($modules === false) {
                return false;
            }

            foreach ($modules as $mod) {
                $mName = strtolower($mod['name']);
                self::$cache['modid'][$mName] = $mod['id'];
                if (isset($mod['url']) && $mod['url']) {
                    $mdName = strtolower($mod['url']);
                    self::$cache['modid'][$mdName] = $mod['id'];
                }
            }

            if (!isset(self::$cache['modid'][$module])) {
                self::$cache['modid'][$module] = false;

                return false;
            }
        }

        if (isset(self::$cache['modid'][$module])) {
            return self::$cache['modid'][$module];
        }

        return false;
    }

    /**
     * The getInfo method gets information on module.
     *
     * Return array of module information or false if core ( id = 0 ).
     *
     * @param integer $modid The module ID.
     *
     * @return array|boolean Module information array or false.
     */
    public static function getInfo($modid = 0)
    {
        // a $modid of 0 is associated with the core ( blocks.mid, ... ).
        if (!is_numeric($modid)) {
            return false;
        }

        if (!is_array(self::$modinfo) || System::isInstalling()) {
            self::$modinfo = self::getModsTable();

            if (!self::$modinfo) {
                return null;
            }

            if (!isset(self::$modinfo[$modid])) {
                self::$modinfo[$modid] = false;

                return self::$modinfo[$modid];
            }
        }

        if (isset(self::$modinfo[$modid])) {
            return self::$modinfo[$modid];
        }

        return false;
    }

    /**
     * The getUserMods method gets a list of user modules.
     *
     * @deprecated see {@link ModUtil::getModulesCapableOf()}
     *
     * @return array An array of module information arrays.
     */
    public static function getUserMods()
    {
        return self::getTypeMods('user');
    }

    /**
     * The getProfileMods method gets a list of profile modules.
     *
     * @deprecated see {@link ModUtil::getModulesCapableOf()}
     *
     * @return array An array of module information arrays.
     */
    public static function getProfileMods()
    {
        return self::getTypeMods('profile');
    }

    /**
     * The getMessageMods method gets a list of message modules.
     *
     * @return array An array of module information arrays.
     */
    public static function getMessageMods()
    {
        return self::getTypeMods('message');
    }

    /**
     * The getAdminMods method gets a list of administration modules.
     *
     * @deprecated see {@link ModUtil::getModulesCapableOf()}
     *
     * @return array An array of module information arrays.
     */
    public static function getAdminMods()
    {
        return self::getTypeMods('admin');
    }

    /**
     * The getTypeMods method gets a list of modules by module type.
     *
     * @param string $capability The module type to get (either 'user' or 'admin') (optional) (default='user').
     *
     * @return array An array of module information arrays.
     */
    public static function getModulesCapableOf($capability = 'user')
    {
        if (!isset(self::$cache['modcache'])) {
            self::$cache['modcache'] = array();
        }

        if (!isset(self::$cache['modcache'][$capability]) || !self::$cache['modcache'][$capability]) {
            self::$cache['modcache'][$capability] = array();
            $mods = self::getAllMods();
            foreach ($mods as $key => $mod) {
                if (isset($mod['capabilities'][$capability])) {
                    self::$cache['modcache'][$capability][] = $mods[$key];
                }
            }
        }

        return self::$cache['modcache'][$capability];
    }

    /**
     * Get mod types.
     *
     * @param string $type The module type, roughly equivalent, now, to a capability.
     *
     * @deprecated see {@link ModUtil::getModulesCapableOf()}
     *
     * @return array An array of module information arrays.
     */
    public static function getTypeMods($type = 'user')
    {
        return self::getModulesCapableOf($type);
    }

    /**
     * Indicates whether the specified module has the specified capability.
     *
     * @param string $module     The name of the module.
     * @param string $capability The name of the advertised capability.
     *
     * @return boolean True if the specified module advertises that it has the specified capability, otherwise false.
     */
    public static function isCapable($module, $capability)
    {
        $modinfo = self::getInfoFromName($module);
        if (!$modinfo) {
            return false;
        }

        return (bool)array_key_exists($capability, $modinfo['capabilities']);
    }

    /**
     * Retrieves the capabilities of the specified module.
     *
     * @param string $module The module name.
     *
     * @return array|boolean The capabilities array, false if the module does not advertise any capabilities.
     */
    public static function getCapabilitiesOf($module)
    {
        $modules = self::getAllMods();
        if (array_key_exists($module, $modules)) {
            return $modules[$module]['capabilities'];
        }

        return false;
    }

    /**
     * The getAllMods method gets a list of all modules.
     *
     * @return array An array of module information arrays.
     */
    public static function getAllMods()
    {
        if (!isset(self::$cache['modsarray'])) {
            self::$cache['modsarray'] = array();
        }

        if (empty(self::$cache['modsarray'])) {
            $all = self::getModsTable();
            foreach ($all as $key => $mod) {
                // "Core" modules should be returned in this list
                if (($mod['state'] == self::STATE_ACTIVE)
                    || (preg_match('/^(extensions|admin|theme|block|groups|permissions|users)$/i', $mod['name'])
                        && ($mod['state'] == self::STATE_UPGRADED || $mod['state'] == self::STATE_INACTIVE))) {
                    self::$cache['modsarray'][$mod['name']] = $mod;
                }
            }
        }

        return self::$cache['modsarray'];
    }

    /**
     * Loads database definition for a module.
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

        // validate
        if (!System::varValidate($modname, 'mod')) {
            return false;
        }

        $serviceManager = ServiceUtil::getManager();

        if (!isset($serviceManager['modutil.dbinfoload.loaded'])) {
            $serviceManager['modutil.dbinfoload.loaded'] = array();
        }

        $loaded = $serviceManager['modutil.dbinfoload.loaded'];

        // check to ensure we aren't doing this twice
        if (isset($loaded[$modname]) && !$force) {
            return $loaded[$modname];
        }

        // from here the module dbinfo will be loaded no doubt
        $loaded[$modname] = true;
        $serviceManager['modutil.dbinfoload.loaded'] = $loaded;

        // get the directory if we don't already have it
        if (empty($directory)) {
            // get the module info
            $modinfo = self::getInfo(self::getIdFromName($modname));
            $directory = $modinfo['directory'];

            $modpath = ($modinfo['type'] == self::TYPE_SYSTEM) ? 'system' : 'modules';
        } else {
            $modpath = is_dir("system/$directory") ? 'system' : 'modules';
        }

        // Load the database definition if required
        $files = array();
        $files[] = "$modpath/$directory/tables.php";
        $files[] = "$modpath/$directory/pntables.php";

        if (Loader::loadOneFile($files)) {
            // If not gets here, the module has no tables to load
            $tablefunc = $modname . '_tables';
            $tablefuncOld = $modname . '_pntables';
            if (function_exists($tablefunc)) {
                $data = call_user_func($tablefunc);
            } elseif (function_exists($tablefuncOld)) {
                $data = call_user_func($tablefuncOld);
            }

            // Generate _column automatically from _column_def if it is not present.
            foreach ($data as $key => $value) {
                $table_col = substr($key, 0, -4);
                if (substr($key, -11) == "_column_def" && !isset($data[$table_col])) {
                    foreach ($value as $fieldname => $def) {
                        $data[$table_col][$fieldname] = $fieldname;
                    }
                }
            }

            if (!isset($serviceManager['dbtables'])) {
                $serviceManager['dbtables'] = array();
            }

            $dbtables = $serviceManager['dbtables'];
            $serviceManager['dbtables'] = array_merge($dbtables, (array)$data);
        } else {
            // the module is tableless (Doctrine or doesn't use tables at all)
            return true;
        }

        // update the loaded status
        $serviceManager['modutil.dbinfoload.loaded'] = $loaded;

        return isset($data) ? $data : $loaded[$modname];
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
     * This loads/set's up a module.  For classic style modules, it tests to see
     * if the module type files exist, admin.php, user.php etc and includes them.
     * If they do not exist, it will return false.
     *
     * Loading a module simply means making the functions/methods available
     * by loading the files and other tasks like binding any language domain.
     *
     * For OO style modules this means registering the main module autoloader,
     * and binding any language domain.
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

        if (!isset(self::$cache['loaded'])) {
            self::$cache['loaded'] = array();
        }

        if (!empty(self::$cache['loaded'][$modtype])) {
            // Already loaded from somewhere else
            return self::$cache['loaded'][$modtype];
        }

        // this is essential to call separately and not in the condition below - drak
        $available = self::available($modname, $force);

        // check the modules state
        if (!$force && !$available) {
            return false;
        }

        // get the module info
        $modinfo = self::getInfo(self::getIdFromName($modname));
        // check for bad System::varValidate($modname)
        if (!$modinfo) {
            return false;
        }

        // create variables for the OS preped version of the directory
        $modpath = ($modinfo['type'] == self::TYPE_SYSTEM) ? 'system' : 'modules';

        // if class is loadable or has been loaded exit here.
        if (self::isInitialized($modname)) {
            self::_loadStyleSheets($modname, $api, $type);

            return $modname;
        }

        // is OOP module
        if (self::isOO($modname)) {
            self::initOOModule($modname);
        } else {
            $osdir = DataUtil::formatForOS($modinfo['directory']);
            $ostype = DataUtil::formatForOS($type);

            $cosfile = "config/functions/$osdir/pn{$ostype}{$osapi}.php";
            $mosfile = "$modpath/$osdir/pn{$ostype}{$osapi}.php";
            $mosdir = "$modpath/$osdir/pn{$ostype}{$osapi}";

            if (file_exists($cosfile)) {
                // Load the file from config
                include_once $cosfile;
            } elseif (file_exists($mosfile)) {
                // Load the file from modules
                include_once $mosfile;
            } elseif (is_dir($mosdir)) {

            } else {
                // File does not exist
                return false;
            }
        }

        self::$cache['loaded'][$modtype] = $modname;

        if ($modinfo['type'] == self::TYPE_MODULE) {
            ZLanguage::bindModuleDomain($modname);
        }

        // Load database info
        self::dbInfoLoad($modname, $modinfo['directory']);

        self::_loadStyleSheets($modname, $api, $type);

        $event = new Zikula_Event('module_dispatch.postloadgeneric', null, array('modinfo' => $modinfo, 'type' => $type, 'force' => $force, 'api' => $api));
        EventUtil::notify($event);

        return $modname;
    }

    /**
     * Initialise all modules.
     *
     * @return void
     */
    public static function loadAll()
    {
        $modules = self::getModsTable();
        unset($modules[0]);
        foreach ($modules as $module) {
            if (self::available($module['name'])) {
                self::loadGeneric($module['name']);
            }
        }
    }

    /**
     * Add stylesheet to the page vars.
     *
     * This makes the modulestylesheet plugin obsolete,
     * but only for non-api loads as we would pollute the stylesheets
     * not during installation as the Theme engine may not be available yet and not for system themes
     * TODO: figure out how to determine if a userapi belongs to a hook module and load the
     *       corresponding css, perhaps with a new entry in modules table?
     *
     * @param string  $modname Module name.
     * @param boolean $api     Whether or not it's a api load.
     * @param string  $type    Type.
     *
     * @return void
     */
    private static function _loadStyleSheets($modname, $api, $type)
    {
        if (!System::isInstalling() && !$api) {
            PageUtil::addVar('stylesheet', ThemeUtil::getModuleStylesheet($modname));
            if (strpos($type, 'admin') === 0) {
                // load special admin stylesheets for administrator controllers
                PageUtil::addVar('stylesheet', ThemeUtil::getModuleStylesheet('Admin'));
            }
        }
    }

    /**
     * Get module class.
     *
     * @param string  $modname Module name.
     * @param string  $type    Type.
     * @param boolean $api     Whether or not to get the api class.
     * @param boolean $force   Whether or not to force load.
     *
     * @return boolean|string Class name.
     */
    public static function getClass($modname, $type, $api = false, $force = false)
    {
        // do not cache this process - drak
        if (!self::isOO($modname)) {
            return false;
        }

        if ($api) {
            $result = self::loadApi($modname, $type);
        } else {
            $result = self::load($modname, $type);
        }

        if (!$result) {
            return false;
        }

        $modinfo = self::getInfo(self::getIDFromName($modname));

        $className = ($api) ? ucwords($modname) . '_Api_' . ucwords($type) : ucwords($modname) . '_Controller_' . ucwords($type);

        // allow overriding the OO class (to override existing methods using inheritance).
        $event = new Zikula_Event('module_dispatch.custom_classname', null, array('modname', 'modinfo' => $modinfo, 'type' => $type, 'api' => $api), $className);
        EventUtil::notify($event);
        if ($event->isStopped()) {
            $className = $event->getData();
        }

        // check the modules state
        if (!$force && !self::available($modname)) {
            return false;
        }

        if (class_exists($className)) {
            return $className;
        }

        return false;
    }

    /**
     * Checks if module has the given controller.
     *
     * @param string $modname Module name.
     * @param string $type    Controller type.
     *
     * @return boolean
     */
    public static function hasController($modname, $type)
    {
        return (bool)self::getClass($modname, $type);
    }

    /**
     * Checks if module has the given API class.
     *
     * @param string $modname Module name.
     * @param string $type    API type.
     *
     * @return boolean
     */
    public static function hasApi($modname, $type)
    {
        return (bool)self::getClass($modname, $type, true);
    }

    /**
     * Get class object.
     *
     * @param string $className Class name.
     *
     * @throws LogicException If $className is neither a Zikula_AbstractApi nor a Zikula_AbstractController.
     * @return object         Module object.
     */
    public static function getObject($className)
    {
        if (!$className) {
            return false;
        }

        $serviceId = strtolower("module.$className");
        $sm = ServiceUtil::getManager();

        $callable = false;
        if ($sm->hasService($serviceId)) {
            $object = $sm->getService($serviceId);
        } else {
            $r = new ReflectionClass($className);
            $object = $r->newInstanceArgs(array($sm));
            try {
                if (strrpos($className, 'Api') && !$object instanceof Zikula_AbstractApi) {
                    throw new LogicException(sprintf('Api %s must inherit from Zikula_AbstractApi', $className));
                } elseif (!strrpos($className, 'Api') && !$object instanceof Zikula_AbstractController) {
                    throw new LogicException(sprintf('Controller %s must inherit from Zikula_AbstractController', $className));
                }
            } catch (LogicException $e) {
                if (System::isDevelopmentMode()) {
                    throw $e;
                } else {
                    LogUtil::registerError('A fatal error has occured which can be viewed only in development mode.', 500);

                    return false;
                }
            }
            $sm->attachService(strtolower($serviceId), $object);
        }

        return $object;
    }

    /**
     * Get info if callable.
     *
     * @param string  $modname Module name.
     * @param string  $type    Type.
     * @param string  $func    Function.
     * @param boolean $api     Whether or not this is an api call.
     * @param boolean $force   Whether or not force load.
     *
     * @return mixed
     */
    public static function getCallable($modname, $type, $func, $api = false, $force = false)
    {
        $className = self::getClass($modname, $type, $api, $force);
        if (!$className) {
            return false;
        }

        $object = self::getObject($className);
        if (is_callable(array($object, $func))) {
            return array('serviceid' => strtolower("module.$className"), 'classname' => $className, 'callable' => array($object, $func));
        }

        return false;
    }

    /**
     * Run a module function.
     *
     * @param string  $modname    The name of the module.
     * @param string  $type       The type of function to run.
     * @param string  $func       The specific function to run.
     * @param array   $args       The arguments to pass to the function.
     * @param boolean $api        Whether or not to execute an API (or regular) function.
     * @param string  $instanceof Perform instanceof checking of target class.
     *
     * @throws Zikula_Exception_NotFound If method was not found.
     * @throws InvalidArgumentException  If the controller is not an instance of the class specified in $instanceof.
     *
     * @return mixed.
     */
    public static function exec($modname, $type = 'user', $func = 'main', $args = array(), $api = false, $instanceof = null)
    {
        // define input, all numbers and booleans to strings
        $modname = isset($modname) ? ((string)$modname) : '';
        $ftype = ($api ? 'api' : '');
        $loadfunc = ($api ? 'ModUtil::loadApi' : 'ModUtil::load');

        // validate
        if (!System::varValidate($modname, 'mod')) {
            return null;
        }

        // Remove from 1.4
        if (System::isLegacyMode() && $modname == 'Modules') {
            LogUtil::log(__('Warning! "Modules" module has been renamed to "Extensions".  Please update your ModUtil::func() and ModUtil::apiFunc() calls.'));
            $modname = 'Extensions';
        }

        $modinfo = self::getInfo(self::getIDFromName($modname));
        $path = ($modinfo['type'] == self::TYPE_SYSTEM ? 'system' : 'modules');

        $controller = null;
        $modfunc = null;
        $loaded = call_user_func_array($loadfunc, array($modname, $type));
        if (self::isOO($modname)) {
            $result = self::getCallable($modname, $type, $func, $api);
            if ($result) {
                $modfunc = $result['callable'];
                $controller = $modfunc[0];
                if (!is_null($instanceof)) {
                    if (!$controller instanceof $instanceof) {
                        throw new InvalidArgumentException(__f('%1$s must be an instance of $2$s', array(get_class($controller), $instanceof)));
                    }
                }
            }
        }

        $modfunc = ($modfunc) ? $modfunc : "{$modname}_{$type}{$ftype}_{$func}";
        $eventManager = EventUtil::getManager();
        if ($loaded) {
            $preExecuteEvent = new Zikula_Event('module_dispatch.preexecute', $controller, array('modname' => $modname, 'modfunc' => $modfunc, 'args' => $args, 'modinfo' => $modinfo, 'type' => $type, 'api' => $api));
            $postExecuteEvent = new Zikula_Event('module_dispatch.postexecute', $controller, array('modname' => $modname, 'modfunc' => $modfunc, 'args' => $args, 'modinfo' => $modinfo, 'type' => $type, 'api' => $api));

            if (is_callable($modfunc)) {
                $eventManager->notify($preExecuteEvent);

                // Check $modfunc is an object instance (OO) or a function (old)
                if (is_array($modfunc)) {
                    if ($modfunc[0] instanceof Zikula_AbstractController) {
                        $reflection = call_user_func(array($modfunc[0], 'getReflection'));
                        $subclassOfReflection = new ReflectionClass($reflection->getParentClass());
                        if ($subclassOfReflection->hasMethod($modfunc[1])) {
                            // Don't allow front controller to access any public methods inside the controller's parents
                            throw new Zikula_Exception_NotFound();
                        }
                        $modfunc[0]->preDispatch();
                    }

                    $postExecuteEvent->setData(call_user_func($modfunc, $args));
                    if ($modfunc[0] instanceof Zikula_AbstractController) {
                        $modfunc[0]->postDispatch();
                    }
                } else {
                    $postExecuteEvent->setData($modfunc($args));
                }

                return $eventManager->notify($postExecuteEvent)->getData();
            }

            // get the theme
            if (ServiceUtil::getManager()->getService('zikula')->getStage() & Zikula_Core::STAGE_THEME) {
                $theme = ThemeUtil::getInfo(ThemeUtil::getIDFromName(UserUtil::getTheme()));
                if (file_exists($file = 'themes/' . $theme['directory'] . '/functions/' . $modname . "/{$type}{$ftype}/$func.php") || file_exists($file = 'themes/' . $theme['directory'] . '/functions/' . $modname . "/pn{$type}{$ftype}/$func.php")) {
                    include_once $file;
                    if (function_exists($modfunc)) {
                        EventUtil::notify($preExecuteEvent);
                        $postExecuteEvent->setData($modfunc($args));

                        return EventUtil::notify($postExecuteEvent)->getData();
                    }
                }
            }

            if (file_exists($file = "config/functions/$modname/{$type}{$ftype}/$func.php") || file_exists($file = "config/functions/$modname/pn{$type}{$ftype}/$func.php")) {
                include_once $file;
                if (is_callable($modfunc)) {
                    $eventManager->notify($preExecuteEvent);
                    $postExecuteEvent->setData($modfunc($args));

                    return $eventManager->notify($postExecuteEvent)->getData();
                }
            }

            if (file_exists($file = "$path/$modname/{$type}{$ftype}/$func.php") || file_exists($file = "$path/$modname/pn{$type}{$ftype}/$func.php")) {
                include_once $file;
                if (is_callable($modfunc)) {
                    $eventManager->notify($preExecuteEvent);
                    $postExecuteEvent->setData($modfunc($args));

                    return $eventManager->notify($postExecuteEvent)->getData();
                }
            }

            // try to load plugin
            // This kind of eventhandler should
            // 1. Check $event['modfunc'] to see if it should run else exit silently.
            // 2. Do something like $result = {$event['modfunc']}({$event['args'});
            // 3. Save the result $event->setData($result).
            // 4. $event->setNotify().
            // return void
            // This event means that no $type was found
            $event = new Zikula_Event('module_dispatch.type_not_found', null, array('modfunc' => $modfunc, 'args' => $args, 'modinfo' => $modinfo, 'type' => $type, 'api' => $api), false);
            $eventManager->notify($event);

            if ($preExecuteEvent->isStopped()) {
                return $preExecuteEvent->getData();
            }

            return false;
        }

        // Issue not found exception for controller requests
        if (!System::isLegacyMode() && !$api) {
            throw new Zikula_Exception_NotFound(__f('The requested controller action %s_Controller_%s::%s() could not be found', array($modname, $type, $func)));
        }
    }

    /**
     * Run a module function.
     *
     * @param string $modname    The name of the module.
     * @param string $type       The type of function to run.
     * @param string $func       The specific function to run.
     * @param array  $args       The arguments to pass to the function.
     * @param string $instanceof Perform instanceof checking of target class.
     *
     * @return mixed.
     */
    public static function func($modname, $type = 'user', $func = 'main', $args = array(), $instanceof = null)
    {
        return self::exec($modname, $type, $func, $args, false, $instanceof);
    }

    /**
     * Run an module API function.
     *
     * @param string $modname    The name of the module.
     * @param string $type       The type of function to run.
     * @param string $func       The specific function to run.
     * @param array  $args       The arguments to pass to the function.
     * @param string $instanceof Perform instanceof checking of target class.
     *
     * @return mixed.
     */
    public static function apiFunc($modname, $type = 'user', $func = 'main', $args = array(), $instanceof = null)
    {
        if (empty($type)) {
            $type = 'user';
        } elseif (!System::varValidate($type, 'api')) {
            return null;
        }

        if (empty($func)) {
            $func = 'main';
        }

        return self::exec($modname, $type, $func, $args, true, $instanceof);
    }

    /**
     * Generate a module function URL.
     *
     * If the module is non-API compliant (type 1) then
     * a) $func is ignored.
     * b) $type=admin will generate admin.php?module=... and $type=user will generate index.php?name=...
     *
     * @param string       $modname The name of the module.
     * @param string       $type    The type of function to run.
     * @param string       $func    The specific function to run.
     * @param array        $args    The array of arguments to put on the URL.
     * @param boolean|null $ssl     Set to constant null,true,false $ssl = true not $ssl = 'true'  null - leave the current status untouched,
     *                                     true - create a ssl url, false - create a non-ssl url.
     * @param string         $fragment     The framgment to target within the URL.
     * @param boolean|null   $fqurl        Fully Qualified URL. True to get full URL, eg for Redirect, else gets root-relative path unless SSL.
     * @param boolean        $forcelongurl Force ModUtil::url to not create a short url even if the system is configured to do so.
     * @param boolean|string $forcelang    Force the inclusion of the $forcelang or default system language in the generated url.
     *
     * @return string Absolute URL for call.
     */
    public static function url($modname, $type = null, $func = null, $args = array(), $ssl = null, $fragment = null, $fqurl = null, $forcelongurl = false, $forcelang=false)
    {
        // define input, all numbers and booleans to strings
        $modname = isset($modname) ? ((string)$modname) : '';

        // note - when this legacy is to be removed, change method signature $type = null to $type making it a required argument.
        if (!$type) {
            if (System::isLegacyMode()) {
                $type = 'user';
                LogUtil::log('ModUtil::url() - $type is a required argument, you must specify it explicitly.', E_USER_DEPRECATED);
            } else {
                throw new UnexpectedValueException('ModUtil::url() - $type is a required argument, you must specify it explicitly.');
            }
        }

        // note - when this legacy is to be removed, change method signature $func = null to $func making it a required argument.
        if (!$func) {
            if (System::isLegacyMode()) {
                $func = 'main';
                LogUtil::log('ModUtil::url() - $func is a required argument, you must specify it explicitly.', E_USER_DEPRECATED);
            } else {
                throw new UnexpectedValueException('ModUtil::url() - $func is a required argument, you must specify it explicitly.');
            }
        }

        // validate
        if (!System::varValidate($modname, 'mod')) {
            return null;
        }

        // Remove from 1.4
        if (System::isLegacyMode() && $modname == 'Modules') {
            LogUtil::log(__('Warning! "Modules" module has been renamed to "Extensions".  Please update your ModUtil::url() or {modurl} calls with $module = "Extensions".'));
            $modname = 'Extensions';
        }

        //get the module info
        $modinfo = self::getInfo(self::getIDFromName($modname));

        // set the module name to the display name if this is present
        if (isset($modinfo['url']) && !empty($modinfo['url'])) {
            $modname = rawurlencode($modinfo['url']);
        }

        $entrypoint = System::getVar('entrypoint');
        $host = System::serverGetVar('HTTP_HOST');

        if (empty($host)) {
            return false;
        }

        $baseuri = System::getBaseUri();
        $https = System::serverGetVar('HTTPS');
        $shorturls = System::getVar('shorturls');
        $shorturlsstripentrypoint = System::getVar('shorturlsstripentrypoint');
        $shorturlsdefaultmodule = System::getVar('shorturlsdefaultmodule');

        // Don't encode URLs with escaped characters, like return urls.
        foreach ($args as $v) {
            if (!is_array($v)) {
                if (strpos($v, '%') !== false) {
                    $shorturls = false;
                    break;
                }
            } else {
                foreach ($v as $vv) {
                    if (is_array($vv)) {
                        foreach ($vv as $vvv) {
                            if (!is_array($vvv) && strpos($vvv, '%') !== false) {
                                $shorturls = false;
                                break;
                            }
                        }
                    } elseif (strpos($vv, '%') !== false) {
                        $shorturls = false;
                        break;
                    }
                }
                break;
            }
        }

        // Setup the language code to use
        if (is_array($args) && isset($args['lang'])) {
            if (in_array($args['lang'], ZLanguage::getInstalledLanguages())) {
                $language = $args['lang'];
            }
            unset($args['lang']);
        }
        if (!isset($language)) {
            $language = ZLanguage::getLanguageCode();
        }

        $language = ($forcelang && in_array($forcelang, ZLanguage::getInstalledLanguages()) ? $forcelang : $language);

        // Only produce full URL when HTTPS is on or $ssl is set
        $siteRoot = '';
        if ((isset($https) && $https == 'on') || $ssl != null || $fqurl == true) {
            $protocol = 'http' . (($https == 'on' && $ssl !== false) || $ssl === true ? 's' : '');
            $secureDomain = System::getVar('secure_domain');
            $siteRoot = $protocol . '://' . (($secureDomain != '') ? $secureDomain : ($host . $baseuri)) . '/';
        }

        // Only convert type=user. Exclude links that append a theme parameter
        if ($shorturls && $type == 'user' && $forcelongurl == false) {
            if (isset($args['theme'])) {
                $theme = $args['theme'];
                unset($args['theme']);
            }
            // Module-specific Short URLs
            $url = self::apiFunc($modinfo['name'], 'user', 'encodeurl', array('modname' => $modname, 'type' => $type, 'func' => $func, 'args' => $args));
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
                $url = $modname . ($vars || $func != 'main' ? "/$func$vars" : '');
            }

            if ($modinfo && $shorturlsdefaultmodule && $shorturlsdefaultmodule == $modinfo['name']) {
                $pattern = '/^'.preg_quote($modinfo['url'], '/').'\//';
                $url = preg_replace($pattern, '', $url);
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
            // Regular stuff
            $urlargs = "module=$modname&type=$type&func=$func";

            // add lang param to URL
            if (ZLanguage::isRequiredLangParam() || $forcelang) {
                $urlargs .= "&lang=$language";
            }

            $url = "$entrypoint?$urlargs";

            if (!is_array($args)) {
                return false;
            } else {
                foreach ($args as $key => $value) {
                    if (is_array($value)) {
                        foreach ($value as $l => $w) {
                            if (is_numeric($w) || !empty($w)) {
                                // we suppress '', but allow 0 as value (see #193)
                                if (is_array($w)) {
                                    foreach ($w as $m => $n) {
                                        if (is_numeric($n) || !empty($n)) {
                                            $n    = strpos($n, '%') !== false ? $n : urlencode($n);
                                            $url .= "&$key" . "[$l][$m]=$n";
                                        }
                                    }
                                } else {
                                    $w    = strpos($w, '%') !== false ? $w : urlencode($w);
                                    $url .= "&$key" . "[$l]=$w";
                                }
                            }
                        }
                    } elseif (is_numeric($value) || !empty($value)) {
                        // we suppress '', but allow 0 as value (see #193)
                        $w    = strpos($value, '%') !== false ? $value : urlencode($value);
                        $url .= "&$key=$value";
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
        if (!System::varValidate($modname, 'mod')) {
            return false;
        }

        if (!isset(self::$cache['modstate'])) {
            self::$cache['modstate'] = array();
        }

        if (!isset(self::$cache['modstate'][$modname]) || $force == true) {
            $modinfo = self::getInfo(self::getIDFromName($modname));
            if (isset($modinfo['state'])) {
                self::$cache['modstate'][$modname] = $modinfo['state'];
            }
        }

        if ($force == true) {
            self::$cache['modstate'][$modname] = self::STATE_ACTIVE;
        }

        if ((isset(self::$cache['modstate'][$modname]) &&
                self::$cache['modstate'][$modname] == self::STATE_ACTIVE) || (preg_match('/^(extensions|admin|theme|block|groups|permissions|users)$/i', $modname) &&
                (isset(self::$cache['modstate'][$modname]) && (self::$cache['modstate'][$modname] == self::STATE_UPGRADED || self::$cache['modstate'][$modname] == self::STATE_INACTIVE)))) {
            self::$cache['modstate'][$modname] = self::STATE_ACTIVE;

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
        if (!isset(self::$cache['modgetname'])) {
            self::$cache['modgetname'] = FormUtil::getPassedValue('module', null, 'GETPOST', FILTER_SANITIZE_STRING);

            if (empty(self::$cache['modgetname'])) {
                if (!System::getVar('startpage')) {
                    self::$cache['modgetname'] = System::getVar('startpage');
                } else {
                    $baseUriLenght = strlen(System::getBaseUri());
                    $shortUrlPath = substr(System::getCurrentUri(),$baseUriLenght+1);
                    if (!empty($shortUrlPath) == 0) {
                        self::$cache['modgetname'] = System::getVar('startpage');
                    } else {
                        $args = explode('/', $shortUrlPath);
                        self::$cache['modgetname'] = $args[0];
                    }
                }
            }

            // the parameters may provide the module alias so lets get
            // the real name from the db
            $modinfo = self::getInfo(self::getIdFromName(self::$cache['modgetname']));
            if (isset($modinfo['name'])) {
                $type = FormUtil::getPassedValue('type', null, 'GETPOST', FILTER_SANITIZE_STRING);

                self::$cache['modgetname'] = $modinfo['name'];

                if ((!$type == 'init' || !$type == 'initeractiveinstaller') && !self::available(self::$cache['modgetname'])) {
                    self::$cache['modgetname'] = System::getVar('startpage');
                }
            }
        }

        return self::$cache['modgetname'];
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
     * @deprecated since 1.3.0
     *
     * @return boolean True if successful, false otherwise.
     */
    public static function registerHook($hookobject, $hookaction, $hookarea, $hookmodule, $hooktype, $hookfunc)
    {
        if (!System::isLegacyMode()) {
            LogUtil::log(__f('%1$s::%2$s is not available in without legacy mode', array('ModUtil', 'registerHook')), Zikula_AbstractErrorHandler::ERR);

            return false;
        }

        // define input, all numbers and booleans to strings
        $hookmodule = isset($hookmodule) ? ((string)$hookmodule) : '';

        // validate
        if (!System::varValidate($hookmodule, 'mod')) {
            return false;
        }

        if (self::isOO($hookmodule)) {
            LogUtil::log(__('OO module types may not make use of this legacy API'), Zikula_AbstractErrorHandler::ERR);

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
     * @deprecated since 1.3.0
     *
     * @return boolean True if successful, false otherwise.
     */
    public static function unregisterHook($hookobject, $hookaction, $hookarea, $hookmodule, $hooktype, $hookfunc)
    {
        // define input, all numbers and booleans to strings
        $hookmodule = isset($hookmodule) ? ((string)$hookmodule) : '';

        // validate
        if (!System::varValidate($hookmodule, 'mod')) {
            return false;
        }

        // Get database info
        $tables = DBUtil::getTables();
        $hookscolumn = $tables['hooks_column'];

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
     * @param boolean $implode    Implode collapses all display hooks into a single string.
     *
     * @deprecated since 1.3.0
     *
     * @return string|array String output from GUI hooks, extrainfo array for API hooks.
     */
    public static function callHooks($hookobject, $hookaction, $hookid, $extrainfo = array(), $implode = true)
    {
        if (!System::isLegacyMode()) {
            return null;
        }

        if (!isset(self::$cache['modulehooks'])) {
            self::$cache['modulehooks'] = array();
        }

        if (!isset($hookaction)) {
            return null;
        }

        if (isset($extrainfo['module']) && (self::available($extrainfo['module']) || strtolower($hookobject) == 'module' || strtolower($extrainfo['module']) == 'zikula')) {
            $modname = $extrainfo['module'];
        } else {
            $modname = self::getName();
        }

        if (self::isOO($modname)) {
            LogUtil::log(__('OO module types may not make use of this legacy API'), Zikula_AbstractErrorHandler::ERR);

            return null;
        }

        $lModname = strtolower($modname);
        if (!isset(self::$cache['modulehooks'][$lModname])) {
            // Get database info
            $tables = DBUtil::getTables();
            $cols = $tables['hooks_column'];
            $where = "WHERE $cols[smodule] = '" . DataUtil::formatForStore($modname) . "'";
            $orderby = "$cols[sequence] ASC";
            $hooks = DBUtil::selectObjectArray('hooks', $where, $orderby);
            self::$cache['modulehooks'][$lModname] = $hooks;
        }

        $gui = false;
        $output = array();

        // Call each hook
        foreach (self::$cache['modulehooks'][$lModname] as $modulehook) {
            if (!isset($extrainfo['tmodule']) || (isset($extrainfo['tmodule']) && $extrainfo['tmodule'] == $modulehook['tmodule'])) {
                if (($modulehook['action'] == $hookaction) && ($modulehook['object'] == $hookobject)) {
                    if (isset($modulehook['tarea']) && $modulehook['tarea'] == 'GUI') {
                        $gui = true;
                        if (self::available($modulehook['tmodule'], $modulehook['ttype']) && self::load($modulehook['tmodule'], $modulehook['ttype'])) {
                            $hookArgs = array('objectid' => $hookid, 'extrainfo' => $extrainfo, 'modulehook' => $modulehook);
                            $output[$modulehook['tmodule']] = self::func($modulehook['tmodule'], $modulehook['ttype'], $modulehook['tfunc'], $hookArgs);
                        }
                    } else {
                        if (isset($modulehook['tmodule']) &&
                                self::available($modulehook['tmodule'], $modulehook['ttype']) &&
                                self::loadApi($modulehook['tmodule'], $modulehook['ttype'])) {
                            $hookArgs = array('objectid' => $hookid, 'extrainfo' => $extrainfo, 'modulehook' => $modulehook);
                            $extrainfo = self::apiFunc($modulehook['tmodule'], $modulehook['ttype'], $modulehook['tfunc'], $hookArgs);
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

            return $output;
        }

        return $extrainfo;
    }

    /**
     * Determine if a module is hooked by another module.
     *
     * @param string $tmodule The target module.
     * @param string $smodule The source module - default the current top most module.
     *
     * @deprecated since 1.3.0
     *
     * @return boolean True if the current module is hooked by the target module, false otherwise.
     */
    public static function isHooked($tmodule, $smodule)
    {
        if (!isset(self::$cache['ishooked'])) {
            self::$cache['ishooked'] = array();
        }

        if (isset(self::$cache['ishooked'][$tmodule][$smodule])) {
            return self::$cache['ishooked'][$tmodule][$smodule];
        }

        // define input, all numbers and booleans to strings
        $tmodule = isset($tmodule) ? ((string)$tmodule) : '';
        $smodule = isset($smodule) ? ((string)$smodule) : '';

        // validate
        if (!System::varValidate($tmodule, 'mod') || !System::varValidate($smodule, 'mod')) {
            return false;
        }

        // Get database info
        $tables = DBUtil::getTables();
        $hookscolumn = $tables['hooks_column'];

        // Get applicable hooks
        $where = "WHERE $hookscolumn[smodule] = '" . DataUtil::formatForStore($smodule) . "'
                    AND $hookscolumn[tmodule] = '" . DataUtil::formatForStore($tmodule) . "'";

        self::$cache['ishooked'][$tmodule][$smodule] = $numitems = DBUtil::selectObjectCount('hooks', $where);
        self::$cache['ishooked'][$tmodule][$smodule] = ($numitems > 0);

        return self::$cache['ishooked'][$tmodule][$smodule];
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
     * include(ModUtil::getBaseDir() . '/includes/private_functions.php');.
     *
     * @param string $modname Name of module to that you want the base directory of.
     *
     * @return string The path from the root directory to the specified module.
     */
    public static function getBaseDir($modname = '')
    {
        if (empty($modname)) {
            $modname = self::getName();
        }

        $path = System::getBaseUri();
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
        if (!isset(self::$cache['modstable'])) {
            self::$cache['modstable'] = array();
        }

        if (!self::$cache['modstable'] || System::isInstalling()) {
            self::$cache['modstable'] = DBUtil::selectObjectArray('modules', '', '', -1, -1, 'id');
            foreach (self::$cache['modstable'] as $mid => $module) {
                if (!isset($module['url']) || empty($module['url'])) {
                    self::$cache['modstable'][$mid]['url'] = $module['displayname'];
                }
                self::$cache['modstable'][$mid]['capabilities'] = unserialize($module['capabilities']);
                self::$cache['modstable'][$mid]['securityschema'] = unserialize($module['securityschema']);
            }
        }

        // add Core module (hack).
        self::$cache['modstable'][0] = array('id' => '0', 'name' => 'zikula', 'type' => self::TYPE_CORE, 'directory' => '', 'displayname' => 'Zikula Core v' . Zikula_Core::VERSION_NUM, 'version' => Zikula_Core::VERSION_NUM, 'state' => self::STATE_ACTIVE);

        return self::$cache['modstable'];
    }

    /**
     * Generic modules select function.
     *
     * Only modules in the module table are returned
     * which means that new/unscanned modules will not be returned.
     *
     * @param string $where The where clause to use for the select.
     * @param string $sort  The sort to use.
     *
     * @return array The resulting module object array.
     */
    public static function getModules($where='', $sort='displayname')
    {
        return DBUtil::selectObjectArray('modules', $where, $sort);
    }

    /**
     * Return an array of modules in the specified state.
     *
     * Only modules in the module table are returned
     * which means that new/unscanned modules will not be returned.
     *
     * @param integer $state The module state (optional) (defaults = active state).
     * @param string  $sort  The sort to use.
     *
     * @return array The resulting module object array.
     */
    public static function getModulesByState($state=self::STATE_ACTIVE, $sort='displayname')
    {
        $tables = DBUtil::getTables();
        $cols = $tables['modules_column'];

        $where = "$cols[state] = $state";

        return DBUtil::selectObjectArray('modules', $where, $sort);
    }

    /**
     * Initialize object oriented module.
     *
     * @param string $moduleName Module name.
     *
     * @return boolean
     */
    public static function initOOModule($moduleName)
    {
        if (self::isInitialized($moduleName)) {
            return true;
        }

        $modinfo = self::getInfo(self::getIdFromName($moduleName));
        if (!$modinfo) {
            return false;
        }

        $modpath = ($modinfo['type'] == self::TYPE_SYSTEM) ? 'system' : 'modules';
        $osdir   = DataUtil::formatForOS($modinfo['directory']);
        ZLoader::addAutoloader($moduleName, realpath("$modpath/$osdir/lib"));
        // load optional bootstrap
        $bootstrap = "$modpath/$osdir/bootstrap.php";
        if (file_exists($bootstrap)) {
            include_once $bootstrap;
        }

        // register any event handlers.
        // module handlers must be attached from the bootstrap.
        if (is_dir("config/EventHandlers/$osdir")) {
            EventUtil::attachCustomHandlers("config/EventHandlers/$osdir");
        }

        // load any plugins
        PluginUtil::loadPlugins("$modpath/$osdir/plugins", "ModulePlugin_{$osdir}");

        self::$ooModules[$moduleName]['initialized'] = true;

        return true;
    }

    /**
     * Checks whether a OO module is initialized.
     *
     * @param string $moduleName Module name.
     *
     * @return boolean
     */
    public static function isInitialized($moduleName)
    {
        return (self::isOO($moduleName) && self::$ooModules[$moduleName]['initialized']);
    }

    /**
     * Checks whether a module is object oriented.
     *
     * @param string $moduleName Module name.
     *
     * @return boolean
     */
    public static function isOO($moduleName)
    {
        if (!isset(self::$ooModules[$moduleName])) {
            self::$ooModules[$moduleName] = array();
            self::$ooModules[$moduleName]['initialized'] = false;
            self::$ooModules[$moduleName]['oo'] = false;
            $modinfo = self::getInfo(self::getIdFromName($moduleName));
            $modpath = ($modinfo['type'] == self::TYPE_SYSTEM) ? 'system' : 'modules';
            $osdir = DataUtil::formatForOS($modinfo['directory']);

            if (!$modinfo) {
                return false;
            }

            if (is_dir("$modpath/$osdir/lib")) {
                self::$ooModules[$moduleName]['oo'] = true;
            }
        }

        return self::$ooModules[$moduleName]['oo'];
    }

    /**
     * Register all autoloaders for all modules.
     *
     * @internal
     *
     * @return void
     */
    public static function registerAutoloaders()
    {
        $modules = self::getModsTable();
        unset($modules[0]);
        foreach ($modules as $module) {
            $base = ($module['type'] == self::TYPE_MODULE) ? 'modules' : 'system';
            $path = "$base/$module[directory]/lib";
            ZLoader::addAutoloader($module['directory'], $path);
        }
    }

    /**
     * Determine the module base directory (system or modules).
     *
     * The purpose of this API is to decouple this calculation from the database,
     * since we ship core with fixed system modules, there is no need to calculate
     * this from the database over and over.
     *
     * @param string $moduleName Module name.
     *
     * @return string Returns 'system' if system module, and 'modules' if not.
     */
    public static function getModuleBaseDir($moduleName)
    {
        if (in_array(strtolower($moduleName), array('admin', 'blocks', 'categories', 'errors', 'extensions', 'groups', 'mailer', 'pagelock', 'permissions', 'search', 'securitycenter', 'settings', 'theme', 'users'))) {
            $directory = 'system';
        } else {
            $directory = 'modules';
        }

        return $directory;
    }

    /**
* Determine the module admin image path.
*
* This function searches for the admin image of a module at several places.
* If no image is found, a default image path is returned.
*
* @param string $moduleName Module name.
*
* @return string Returns module admin image path.
*/
    public static function getModuleImagePath($moduleName)
    {
        if($moduleName == '') {
            return false;
        }
        
        $modinfo = self::getInfoFromName($moduleName);
        $modpath = ($modinfo['type'] == self::TYPE_SYSTEM) ? 'system' : 'modules';
        
        $osmoddir = DataUtil::formatForOS($modinfo['directory']);
        
        $paths = array(
                $modpath . '/' . $osmoddir . '/images/admin.png',
                $modpath . '/' . $osmoddir . '/images/admin.jpg',
                $modpath . '/' . $osmoddir . '/images/admin.gif',
                $modpath . '/' . $osmoddir . '/pnimages/admin.gif',
                $modpath . '/' . $osmoddir . '/pnimages/admin.jpg',
                $modpath . '/' . $osmoddir . '/pnimages/admin.jpeg',
                $modpath . '/' . $osmoddir . '/pnimages/admin.png',
                'system/Admin/images/default.gif'
        );
        
        foreach ($paths as $path) {
            if (is_readable($path)) {
                break;
            }
        }
        
        return $path;
    }
}
