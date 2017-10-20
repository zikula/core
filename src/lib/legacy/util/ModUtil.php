<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerResolver;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Zikula\Core\Event\GenericEvent;
use Zikula\Bundle\CoreBundle\Bundle\Scanner;

/**
 * Module Util.
 * @deprecated
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
    const INCOMPATIBLE_CORE_SHIFT = 20;

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
    protected static $ooModules = [];

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
    protected static $modvars = [];

    /**
     * Internal module cache.
     *
     * @var array
     */
    protected static $cache = [];

    /**
     * Module variables getter.
     *
     * @return ArrayObject
     */
    public static function getModvars()
    {
        @trigger_error('ModUtil class is deprecated, please use VariableApi instead.', E_USER_DEPRECATED);

        return self::$modvars;
    }

    /**
     * Flush this static class' cache.
     *
     * @return void
     */
    public static function flushCache()
    {
        @trigger_error('ModUtil class is deprecated, please use Symfony instead.', E_USER_DEPRECATED);

        self::$cache = [];
    }

    /**
     * The initCoreVars preloads some module vars.
     *
     * Preloads module vars for a number of key modules to reduce sql statements.
     *
     * @param boolean $force
     *
     * @return void
     */
    public static function initCoreVars($force = false)
    {
        @trigger_error('ModUtil class is deprecated, please use VariableApi instead.', E_USER_DEPRECATED);

        // The empty arrays for handlers and settings are required to prevent messages with E_ALL error reporting
        self::$modvars = new ArrayObject([
            EventUtil::HANDLERS => [],
            ServiceUtil::HANDLERS => [],
            'ZikulaSettingsModule' => []
        ]);

        // don't init vars during the installer or upgrader
        if (!$force && !ServiceUtil::getManager()->getParameter('installed')) {
            return;
        }

        // This loads all module variables into the modvars static class variable.
        $em = ServiceUtil::get('doctrine.orm.default_entity_manager');
        /** @var \Zikula\ExtensionsModule\Entity\ExtensionVarEntity[] $modvars */
        $modvars = $em->getRepository('Zikula\ExtensionsModule\Entity\ExtensionVarEntity')->findAll();
        foreach ($modvars as $var) {
            if (!array_key_exists($var->getModname(), self::$modvars)) {
                self::$modvars[$var->getModname()] = [];
            }
            if (array_key_exists($var->getName(), $GLOBALS['ZConfig']['System'])) {
                self::$modvars[$var->getModname()][$var->getName()] = $GLOBALS['ZConfig']['System'][$var->getName()];
            } else {
                self::$modvars[$var->getModname()][$var->getName()] = $var->getValue();
            }
        }

        // Init multilingual variables here, just to have values, even with default site language (page language is set later)
        self::setupMultilingual();

        // Pre-load the module variables array with empty arrays for known modules that
        // do not define any module variables to prevent unnecessary SQL queries to
        // the module_vars table.
        $knownModules = self::getAllMods();
        foreach ($knownModules as $key => $mod) {
            if (!array_key_exists($mod['name'], self::$modvars)) {
                self::$modvars[$mod['name']] = [];
            }
        }
    }

    /**
     * Init variables multilingual dependent.
     *
     * @return boolean True
     */
    public static function setupMultilingual()
    {
        @trigger_error('ModUtil class is deprecated, please use Symfony instead.', E_USER_DEPRECATED);

        // prevent access to missing vars when the system is not installed yet
        if (!\ServiceUtil::getManager()->getParameter('installed')) {
            return true;
        }

        $lang = ZLanguage::getLanguageCode();
        $items = ['sitename', 'slogan', 'metakeywords', 'defaultpagetitle', 'defaultmetadescription'];
        foreach ($items as $item) {
            self::$modvars['ZConfig'][$item] = isset(self::$modvars['ZConfig'][$item . '_' . $lang]) ? self::$modvars['ZConfig'][$item . '_' . $lang] : self::$modvars['ZConfig'][$item . '_en'];
        }

        return true;
    }

    /**
     * Checks to see if a module variable is set.
     * @deprecated at Core-1.4.1
     * @see \Zikula\ExtensionsModule\Api\VariableApi::has()
     * @see service zikula_extensions_module.api.variable
     *
     * @param string $modname The name of the module
     * @param string $name    The name of the variable
     *
     * @return boolean True if the variable exists in the database, false if not
     */
    public static function hasVar($modname, $name)
    {
        @trigger_error('ModUtil class is deprecated, please use VariableApi instead.', E_USER_DEPRECATED);

        // define input, all numbers and booleans to strings
        $modname = static::convertModuleName(isset($modname) ? ((string)$modname) : '');
        $name = isset($name) ? ((string)$name) : '';

        // make sure we have the necessary parameters
        if (!System::varValidate($modname, 'mod') || !System::varValidate($name, 'modvar')) {
            return false;
        }

        // The cast to (array) is for the odd instance where self::$modvars[$modname] is set to null--not sure if this is really needed.
        $varExists = isset(self::$modvars[$modname]) && array_key_exists($name, (array)self::$modvars[$modname]);

        if (!$varExists && \ServiceUtil::getManager()->hasParameter('upgrading') && \ServiceUtil::getManager()->getParameter('upgrading')) {
            // Handle the upgrade edge case--the call to getVar() ensures vars for the module are loaded if newly available.
            $modvars = self::getVar($modname);
            $varExists = array_key_exists($name, (array)$modvars);
        }

        return $varExists;
    }

    /**
     * The getVar method gets a module variable.
     * @deprecated at Core-1.4.1
     * @see \Zikula\ExtensionsModule\Api\VariableApi::get()
     * @see \Zikula\ExtensionsModule\Api\VariableApi::getAll()
     * @see service zikula_extensions_module.api.variable
     *
     * If the name parameter is included then method returns the
     * module variable value.
     * if the name parameter is ommitted then method returns a multi
     * dimentional array of the keys and values for the module vars
     *
     * @param string  $modname The name of the module or pseudo-module (e.g., 'Users', 'ZConfig', '/EventHandlers')
     * @param string  $name    The name of the variable
     * @param mixed   $default The value to return if the requested modvar is not set
     *
     * @return string|array If the name parameter is included then method returns
     *          string - module variable value
     *          if the name parameter is ommitted then method returns
     *          array - multi dimentional array of the keys
     *                  and values for the module vars
     */
    public static function getVar($modname, $name = '', $default = false)
    {
        @trigger_error('ModUtil class is deprecated, please use VariableApi instead.', E_USER_DEPRECATED);

        // if we don't know the modname then lets assume it is the current
        // active module
        if (!isset($modname)) {
            $modname = self::getName();
        }

        $modname = static::convertModuleName($modname);

        // if we haven't got vars for this module (or pseudo-module) yet then lets get them
        if (!array_key_exists($modname, self::$modvars)) {
            // A query out to the database should only be needed if the system is upgrading. Use the installing flag to determine this.
            if (\ServiceUtil::getManager()->hasParameter('upgrading') && \ServiceUtil::getManager()->getParameter('upgrading')) {
                self::initCoreVars(true);
            } else {
                // Prevent a re-query for the same module in the future, where the module does not define any module variables.
                self::$modvars[$modname] = [];
            }
        }

        // if they didn't pass a variable name then return every variable
        // for the specified module as an associative array.
        // ['var1' => value1, 'var2' => value2]
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
     * @deprecated at Core-1.4.1
     * @see \Zikula\ExtensionsModule\Api\VariableApi::set()
     * @see service zikula_extensions_module.api.variable
     *
     * @param string $modname The name of the module
     * @param string $name    The name of the variable
     * @param string $value   The value of the variable
     *
     * @return boolean True if successful, false otherwise
     */
    public static function setVar($modname, $name, $value = '')
    {
        @trigger_error('ModUtil class is deprecated, please use VariableApi instead.', E_USER_DEPRECATED);

        // define input, all numbers and booleans to strings
        $modname = isset($modname) ? ((string)$modname) : '';
        $modname = static::convertModuleName($modname);

        // validate
        if (!System::varValidate($modname, 'mod') || !isset($name)) {
            return false;
        }

        $em = ServiceUtil::get('doctrine.orm.default_entity_manager');
        $entities = $em->getRepository('Zikula\ExtensionsModule\Entity\ExtensionVarEntity')->findBy(['modname' => $modname, 'name' => $name]);
        if (count($entities) > 0) {
            foreach ($entities as $entity) {
                // possible duplicates exist. update all (refs #2385)
                $entity->setValue($value);
            }
        } else {
            $entity = new \Zikula\ExtensionsModule\Entity\ExtensionVarEntity();
            $entity->setModname($modname);
            $entity->setName($name);
            $entity->setValue($value);
            $em->persist($entity);
        }

        self::$modvars[$modname][$name] = $value;

        $em->flush();

        return true;
    }

    /**
     * The setVars method sets multiple module variables.
     * @deprecated at Core-1.4.1
     * @see \Zikula\ExtensionsModule\Api\VariableApi::setAll()
     * @see service zikula_extensions_module.api.variable
     *
     * @param string $modname The name of the module
     * @param array  $vars    An associative array of varnames/varvalues
     *
     * @return boolean True if successful, false otherwise
     */
    public static function setVars($modname, array $vars)
    {
        @trigger_error('ModUtil class is deprecated, please use VariableApi instead.', E_USER_DEPRECATED);

        $ok = true;
        foreach ($vars as $var => $value) {
            $ok = $ok && self::setVar($modname, $var, $value);
        }

        return $ok;
    }

    /**
     * The delVar method deletes a module variable.
     * @deprecated at Core-1.4.1
     * @see \Zikula\ExtensionsModule\Api\VariableApi::del()
     * @see \Zikula\ExtensionsModule\Api\VariableApi::delAll()
     * @see service zikula_extensions_module.api.variable
     *
     * Delete a module variables. If the optional name parameter is not supplied all variables
     * for the module 'modname' are deleted
     *
     * @param string $modname The name of the module
     * @param string $name    The name of the variable (optional)
     *
     * @return boolean True if successful, false otherwise
     */
    public static function delVar($modname, $name = '')
    {
        @trigger_error('ModUtil class is deprecated, please use VariableApi instead.', E_USER_DEPRECATED);

        // define input, all numbers and booleans to strings
        $modname = isset($modname) ? ((string)$modname) : '';
        $modname = static::convertModuleName($modname);

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

        $em = ServiceUtil::get('doctrine.orm.default_entity_manager');

        // if $name is not provided, delete all variables of this module
        // else just delete this specific variable
        /** @var $qb \Doctrine\ORM\QueryBuilder */
        $qb = $em->createQueryBuilder()
                 ->delete('Zikula\ExtensionsModule\Entity\ExtensionVarEntity', 'v')
                 ->where('v.modname = :modname')
                 ->setParameter('modname', $modname);

        if (!empty($name)) {
            $qb->andWhere('v.name = :name')
               ->setParameter('name', $name);
        }

        $query = $qb->getQuery();
        $result = $query->execute();

        return (bool)$result;
    }

    /**
     * Get Module meta info.
     *
     * @param string $module Module name
     *
     * @return array|boolean Module information array or false
     */
    public static function getInfoFromName($module)
    {
        @trigger_error('ModUtil class is deprecated, please use ExtensionApi instead.', E_USER_DEPRECATED);

        return self::getInfo(self::getIdFromName($module));
    }

    /**
     * The getIdFromName method gets module ID given its name.
     *
     * @param string $module The name of the module
     *
     * @return integer module ID
     */
    public static function getIdFromName($module)
    {
        @trigger_error('ModUtil class is deprecated, please use ExtensionApi instead.', E_USER_DEPRECATED);

        // define input, all numbers and booleans to strings
        $alias = (isset($module) ? strtolower((string)$module) : '');
        $module = static::convertModuleName($module);
        $module = (isset($module) ? strtolower((string)$module) : '');

        // validate
        if (!System::varValidate($module, 'mod')) {
            return false;
        }

        if (!isset(self::$cache['modid'])) {
            self::$cache['modid'] = null;
        }

        if (!is_array(self::$cache['modid']) || !\ServiceUtil::getManager()->getParameter('installed')) {
            $modules = self::getModsTable();

            if ($modules === false) {
                return false;
            }

            foreach ($modules as $id => $mod) {
                $mName = strtolower($mod['name']);
                self::$cache['modid'][$mName] = $mod['id'];
                if (!$id == 0) {
                    $mdName = strtolower($mod['url']);
                    self::$cache['modid'][$mdName] = $mod['id'];
                }
            }

            if (!isset(self::$cache['modid'][$module]) && !isset(self::$cache['modid'][$alias])) {
                self::$cache['modid'][$module] = false;

                return false;
            }
        }

        if (isset(self::$cache['modid'][$module])) {
            return self::$cache['modid'][$module];
        }

        if (isset(self::$cache['modid'][$alias])) {
            return self::$cache['modid'][$alias];
        }

        return false;
    }

    /**
     * The getInfo method gets information on module.
     *
     * Return array of module information or false if core ( id = 0 ).
     *
     * @param integer $modid The module ID
     *
     * @return array|boolean Module information array or false
     */
    public static function getInfo($modid = 0)
    {
        @trigger_error('ModUtil class is deprecated, please use ExtensionApi instead.', E_USER_DEPRECATED);

        // a $modid of 0 is associated with the core ( blocks.mid, ... ).
        if (!is_numeric($modid)) {
            return false;
        }

        if (!is_array(self::$modinfo) || !\ServiceUtil::getManager()->getParameter('installed')) {
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
     * @return array An array of module information arrays
     */
    public static function getUserMods()
    {
        @trigger_error('ModUtil class is deprecated, please use CapabilityApi instead.', E_USER_DEPRECATED);

        return self::getTypeMods('user');
    }

    /**
     * The getProfileMods method gets a list of profile modules.
     *
     * @deprecated see {container service: zikula_users_module.internal.profile_module_collector}
     *
     * @return array An array of module information arrays
     */
    public static function getProfileMods()
    {
        @trigger_error('ModUtil class is deprecated, please use CapabilityApi instead.', E_USER_DEPRECATED);

        $profileModules = ServiceUtil::get('zikula_users_module.internal.profile_module_collector')->getKeys();
        $return = [];
        $extensionRepo = ServiceUtil::get('zikula_extensions_module.extension_repository');
        foreach ($profileModules as $module) {
            $moduleEntity = $extensionRepo->get($module);
            $return[$moduleEntity->getDisplayname()] = $moduleEntity->getName();
        }

        return $return;
    }

    /**
     * The getMessageMods method gets a list of message modules.
     *
     * @return array An array of module information arrays
     */
    public static function getMessageMods()
    {
        @trigger_error('ModUtil class is deprecated, please use CapabilityApi instead.', E_USER_DEPRECATED);

        return self::getTypeMods('message');
    }

    /**
     * The getAdminMods method gets a list of administration modules.
     *
     * @deprecated see {@link ModUtil::getModulesCapableOf()}
     *
     * @return array An array of module information arrays
     */
    public static function getAdminMods()
    {
        @trigger_error('ModUtil class is deprecated, please use CapabilityApi instead.', E_USER_DEPRECATED);

        return self::getTypeMods('admin');
    }

    /**
     * The getTypeMods method gets a list of modules by module type.
     * @deprecated at Core-2.0
     * @see \Zikula\ExtensionsModule\Api\CapabilityApi::getExtensionsCapableOf()
     * @see service zikula_extensions_module.api.capability
     *
     * @param string $capability The module type to get (either 'user' or 'admin') (optional) (default='user')
     *
     * @return array An array of module information arrays
     */
    public static function getModulesCapableOf($capability = 'user')
    {
        @trigger_error('ModUtil class is deprecated, please use CapabilityApi instead.', E_USER_DEPRECATED);

        if (!isset(self::$cache['modcache'])) {
            self::$cache['modcache'] = [];
        }

        if (!isset(self::$cache['modcache'][$capability]) || !self::$cache['modcache'][$capability]) {
            self::$cache['modcache'][$capability] = [];
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
     * @param string $type The module type, roughly equivalent, now, to a capability
     *
     * @deprecated see {@link ModUtil::getModulesCapableOf()}
     *
     * @return array An array of module information arrays
     */
    public static function getTypeMods($type = 'user')
    {
        @trigger_error('ModUtil class is deprecated, please use CapabilityApi instead.', E_USER_DEPRECATED);

        return self::getModulesCapableOf($type);
    }

    /**
     * Indicates whether the specified module has the specified capability.
     * @deprecated at Core-2.0
     * @see \Zikula\ExtensionsModule\Api\CapabilityApi::isCapable()
     * @see service zikula_extensions_module.api.capability
     *
     * @param string $module     The name of the module
     * @param string $capability The name of the advertised capability
     *
     * @return boolean True if the specified module advertises that it has the specified capability, otherwise false
     */
    public static function isCapable($module, $capability)
    {
        @trigger_error('ModUtil class is deprecated, please use CapabilityApi instead.', E_USER_DEPRECATED);

        $modinfo = self::getInfoFromName($module);
        if (!$modinfo) {
            return false;
        }

        return (bool)array_key_exists($capability, $modinfo['capabilities']);
    }

    /**
     * Retrieves the capabilities of the specified module.
     * @deprecated at Core-2.0
     * @see \Zikula\ExtensionsModule\Api\CapabilityApi::getCapabilitiesOf()
     * @see service zikula_extensions_module.api.capability
     *
     * @param string $module The module name
     *
     * @return array|boolean The capabilities array, false if the module does not advertise any capabilities
     */
    public static function getCapabilitiesOf($module)
    {
        @trigger_error('ModUtil class is deprecated, please use CapabilityApi instead.', E_USER_DEPRECATED);

        $modules = self::getAllMods();
        if (array_key_exists($module, $modules)) {
            return $modules[$module]['capabilities'];
        }

        return false;
    }

    /**
     * The getAllMods method gets a list of all modules.
     *
     * @return array An array of module information arrays
     */
    public static function getAllMods()
    {
        @trigger_error('ModUtil class is deprecated, please use ExtensionApi instead.', E_USER_DEPRECATED);

        if (!isset(self::$cache['modsarray'])) {
            self::$cache['modsarray'] = [];
        }

        if (empty(self::$cache['modsarray'])) {
            $all = self::getModsTable();
            foreach ($all as $mod) {
                // "Core" modules should be returned in this list
                if (($mod['state'] == self::STATE_ACTIVE)
                    || (preg_match('/^(zikulaextensionsmodule|zikulaadminmodule|zikulathememodule|zikulablockmodule|zikulagroupsmodule|zikulapermissionsmodule|zikulausersmodule)$/i', $mod['name'])
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
     * @param string  $modname   The name of the module to load database definition for
     * @param string  $directory Directory that module is in (if known)
     * @param boolean $force     Force table information to be reloaded
     *
     * @return boolean True if successful, false otherwise
     */
    public static function dbInfoLoad($modname, $directory = '', $force = false)
    {
        @trigger_error('ModUtil class is deprecated, please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $moduleName = $modname;
        // define input, all numbers and booleans to strings
        $modname = (isset($modname) ? strtolower((string)$modname) : '');
        $modname = static::convertModuleName($modname);
        $directory = static::convertModuleName($directory);

        // validate
        if (!System::varValidate($modname, 'mod')) {
            return false;
        }

        $serviceManager = ServiceUtil::getManager();

        if (!isset($serviceManager['modutil.dbinfoload.loaded'])) {
            $serviceManager['modutil.dbinfoload.loaded'] = [];
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

        // all system modules do not require tables.php, return immediately
        // but (old) third party modules may require search table info
        if ($modpath == 'system' && $modname != 'zikulasearchmodule') {
            return true;
        }

        // Load the database definition if required
        $files = [];
        if ($module = self::getModule($moduleName)) {
            $files[] = $module->getPath() . '/tables.php';
        }

        $files[] = "$modpath/$directory/tables.php";
        $files[] = "$modpath/$directory/pntables.php";
        $found = false;
        foreach ($files as $file) {
            if ($found = is_readable($file)) {
                include_once $file;
                break;
            }
        }

        if ($found) {
            // If not gets here, the module has no tables to load
            $tablefunc = $modname . '_tables';
            $tablefuncOld = $modname . '_pntables';
            $data = [];
            if (function_exists($tablefunc)) {
                $data = call_user_func($tablefunc);
            } elseif (function_exists($tablefuncOld)) {
                $data = call_user_func($tablefuncOld);
            }

            /** @var $connection Doctrine\DBAL\Connection */
            $connection = $serviceManager->get('doctrine.dbal.default_connection');
            $dbDriverName = $connection->getDriver()->getName();

            // Generate _column automatically from _column_def if it is not present.
            foreach ($data as $key => $value) {
                $table_col = substr($key, 0, -4);
                if (substr($key, -11) == "_column_def" && !isset($data[$table_col])) {
                    foreach ($value as $fieldname => $def) {
                        $data[$table_col][$fieldname] = $fieldname;
                    }
                }

                if ($dbDriverName == 'derby' || $dbDriverName == 'splice' || $dbDriverName == 'jdbcbridge') {
                    if (substr($key, -7) == '_column') {
                        if (is_array($value) && $value) {
                            foreach ($value as $alias => $fieldname) {
                                if ($alias != 'user') {
                                    $data[$key][$alias] = strtoupper($fieldname);
                                }
                            }
                        }
                    }
                }
            }

            if (!isset($serviceManager['dbtables'])) {
                $serviceManager['dbtables'] = [];
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
     * @param string  $modname The name of the module
     * @param string  $type    The type of functions to load
     * @param boolean $force   Determines to load Module even if module isn't active
     *
     * @return string|boolean Name of module loaded, or false on failure
     */
    public static function load($modname, $type = 'user', $force = false)
    {
        @trigger_error('ModUtil class is deprecated, please use Symfony instead.', E_USER_DEPRECATED);

        if (strtolower(substr($type, -3)) == 'api') {
            return false;
        }

        return self::loadGeneric($modname, $type, $force);
    }

    /**
     * Load an API module.
     *
     * @param string  $modname The name of the module
     * @param string  $type    The type of functions to load
     * @param boolean $force   Determines to load Module even if module isn't active
     *
     * @return string|boolean Name of module loaded, or false on failure
     */
    public static function loadApi($modname, $type = 'user', $force = false)
    {
        @trigger_error('ModUtil class is deprecated, please use Symfony instead.', E_USER_DEPRECATED);

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
     * @param string  $modname The name of the module
     * @param string  $type    The type of functions to load
     * @param boolean $force   Determines to load Module even if module isn't active
     * @param boolean $api     Whether or not to load an API (or regular) module
     *
     * @return string|boolean Name of module loaded, or false on failure
     */
    public static function loadGeneric($modname, $type = 'user', $force = false, $api = false)
    {
        @trigger_error('ModUtil class is deprecated, please use Symfony instead.', E_USER_DEPRECATED);

        // define input, all numbers and booleans to strings
        $osapi = ($api ? 'api' : '');
        $modname = isset($modname) ? ((string)$modname) : '';
        $modname = static::convertModuleName($modname);
        $modtype = strtolower("$modname{$type}{$osapi}");

        if (!isset(self::$cache['loaded'])) {
            self::$cache['loaded'] = [];
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

        self::initOOModule($modname);

        self::$cache['loaded'][$modtype] = $modname;

        if ($modinfo['type'] == self::TYPE_MODULE) {
            ZLanguage::bindModuleDomain($modname);
        }

        // Load database info
        self::dbInfoLoad($modname, $modinfo['directory']);

        self::_loadStyleSheets($modname, $api, $type);

        $event = new GenericEvent(null, ['modinfo' => $modinfo, 'type' => $type, 'force' => $force, 'api' => $api]);
        EventUtil::dispatch('module_dispatch.postloadgeneric', $event);

        return $modname;
    }

    /**
     * Initialise all modules.
     *
     * @return void
     */
    public static function loadAll()
    {
        @trigger_error('ModUtil class is deprecated, please use Symfony instead.', E_USER_DEPRECATED);

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
     * @param string  $modname Module name
     * @param boolean $api     Whether or not it's a api load
     * @param string  $type    Type
     *
     * @return void
     */
    private static function _loadStyleSheets($modname, $api, $type)
    {
        if (\ServiceUtil::getManager()->getParameter('installed') && !$api) {
            $moduleStylesheet = ThemeUtil::getModuleStylesheet($modname);
            if (!empty($moduleStylesheet)) {
                PageUtil::addVar('stylesheet', $moduleStylesheet);
            }
            if (strpos($type, 'admin') === 0) {
                // load special admin stylesheets for administrator controllers
                PageUtil::addVar('stylesheet', ThemeUtil::getModuleStylesheet('ZikulaAdminModule'));
            }
        }
    }

    /**
     * Get module class.
     *
     * @param string  $modname Module name
     * @param string  $type    Type
     * @param boolean $api     Whether or not to get the api class
     * @param boolean $force   Whether or not to force load
     *
     * @return boolean|string Class name
     */
    public static function getClass($modname, $type, $api = false, $force = false)
    {
        @trigger_error('ModUtil class is deprecated, please use Symfony instead.', E_USER_DEPRECATED);

        if ($api) {
            $result = self::loadApi($modname, $type);
        } else {
            $result = self::load($modname, $type);
        }

        if (!$result) {
            return false;
        }

        $modinfo = self::getInfo(self::getIDFromName($modname));

        if ($module = self::getModule($modname)) {
            $ns = $module->getNamespace();
            $className = ($api) ? $ns . '\\Api\\' . ucwords($type) . 'Api' : $ns . '\\Controller\\' . ucwords($type) . 'Controller';
        } else {
            $className = ($api) ? ucwords($modname) . '\\Api\\' . ucwords($type) . 'Api' : ucwords($modname) . '\\Controller\\' . ucwords($type) . 'Controller';
            $classNameOld = ($api) ? ucwords($modname) . '_Api_' . ucwords($type) : ucwords($modname) . '_Controller_' . ucwords($type);
            $className = class_exists($className) ? $className : $classNameOld;
        }

        // allow overriding the OO class (to override existing methods using inheritance).
        $event = new GenericEvent(null, ['modname' => $modname, 'modinfo' => $modinfo, 'type' => $type, 'api' => $api], $className);
        EventUtil::dispatch('module_dispatch.custom_classname', $event);
        if ($event->isPropagationStopped()) {
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
     * @param string $modname Module name
     * @param string $type    Controller type
     *
     * @return boolean
     */
    public static function hasController($modname, $type)
    {
        @trigger_error('ModUtil class is deprecated, please use Symfony instead.', E_USER_DEPRECATED);

        return (bool)self::getClass($modname, $type);
    }

    /**
     * Checks if module has the given API class.
     *
     * @param string $modname Module name
     * @param string $type    API type
     *
     * @return boolean
     */
    public static function hasApi($modname, $type)
    {
        @trigger_error('ModUtil class is deprecated, please use Symfony instead.', E_USER_DEPRECATED);

        return (bool)self::getClass($modname, $type, true);
    }

    /**
     * Get class object.
     *
     * @param string $className Class name
     * @param string $modname
     *
     * @return object Module object
     */
    public static function getObject($className, $modname)
    {
        @trigger_error('ModUtil class is deprecated, please use Symfony instead.', E_USER_DEPRECATED);

        if (!$className) {
            return false;
        }

        $serviceId = str_replace('\\', '_', strtolower("module.$className"));
        $sm = ServiceUtil::getManager();

        if ($sm->has($serviceId)) {
            $object = $sm->get($serviceId);
        } else {
            $r = new ReflectionClass($className);
            if ($r->hasMethod('__construct') && $r->isSubclassOf('Zikula_AbstractBase')) {
                $object = $r->newInstanceArgs([$sm, self::getModule($modname)]);
            } elseif ($r->hasMethod('__construct') && $r->isSubclassOf('Zikula\Core\Controller\AbstractController')) {
                $object = $r->newInstanceArgs([self::getModule($modname)]);
            } else {
                $object = $r->newInstance();
            }

            if ($object instanceof ContainerAwareInterface) {
                $object->setContainer(ServiceUtil::getManager());
            }

            if (method_exists($object, 'setTranslator')) {
                $object->setTranslator(
                    new \Zikula\Common\I18n\Translator(self::getModule($modname)->getTranslationDomain())
                );
            }

            $sm->set($serviceId, $object);
        }

        return $object;
    }

    /**
     * Get info if callable.
     *
     * @param string  $modname Module name
     * @param string  $type    Type
     * @param string  $func    Function
     * @param boolean $api     Whether or not this is an api call
     * @param boolean $force   Whether or not force load
     *
     * @return mixed
     */
    public static function getCallable($modname, $type, $func, $api = false, $force = false)
    {
        @trigger_error('ModUtil class is deprecated, please use Symfony instead.', E_USER_DEPRECATED);

        $className = self::getClass($modname, $type, $api, $force);
        if (!$className) {
            return false;
        }

        $object = self::getObject($className, $modname);
        $action = $api ? '' : 'Action';
        if (!is_callable([$object, $func . $action])) {
            return false;
        }

        return [
            'serviceid' => strtolower('module.' . $className),
            'classname' => $className,
            'callable' => [$object, $func . $action]
        ];
    }

    /**
     * Run a module function.
     *
     * @param string  $modname    The name of the module
     * @param string  $type       The type of function to run
     * @param string  $func       The specific function to run
     * @param array   $args       The arguments to pass to the function
     * @param boolean $api        Whether or not to execute an API (or regular) function
     * @param string  $instanceof Perform instanceof checking of target class
     *
     * @throws Zikula_Exception_NotFound If method was not found
     * @throws InvalidArgumentException  If the controller is not an instance of the class specified in $instanceof
     *
     * @return mixed
     */
    public static function exec($modname, $type = 'user', $func = 'main', $args = [], $api = false, $instanceof = null)
    {
        @trigger_error('ModUtil class is deprecated, please use Symfony instead.', E_USER_DEPRECATED);

        // define input, all numbers and booleans to strings
        $modname = isset($modname) ? ((string)$modname) : '';
        $modname = static::convertModuleName($modname);

        // validate
        if (!System::varValidate($modname, 'mod')) {
            return null;
        }

        $modinfo = self::getInfo(self::getIDFromName($modname));

        $controller = null;
        $modfunc = null;
        $loaded = call_user_func_array($api ? 'ModUtil::loadApi' : 'ModUtil::load', [$modname, $type]);
        if (self::isOO($modname)) {
            $result = self::getCallable($modname, $type, $func, $api);
            if ($result) {
                $modfunc = $result['callable'];
                $controller = $modfunc[0];
                if (!is_null($instanceof)) {
                    if (!$controller instanceof $instanceof) {
                        throw new InvalidArgumentException(__f('%1$s must be an instance of $2$s', [get_class($controller), $instanceof]));
                    }
                }
            }
        }

        $eventManager = EventUtil::getManager();
        $sm = ServiceUtil::getManager();
        if ($loaded) {
            $eventArgs = ['modname' => $modname, 'modfunc' => $modfunc, 'args' => $args, 'modinfo' => $modinfo, 'type' => $type, 'api' => $api];
            $preExecuteEvent = new \Zikula\Core\Event\GenericEvent($controller, $eventArgs);
            $postExecuteEvent = new \Zikula\Core\Event\GenericEvent($controller, $eventArgs);

            if (is_callable($modfunc)) {
                $eventManager->dispatch('module_dispatch.preexecute', $preExecuteEvent);

                // Check $modfunc is an object instance (OO) or a function (old)
                if (is_array($modfunc)) {
                    try {
                        self::getModule($modname);
                        $newType = true;
                    } catch (\Exception $e) {
                        $newType = false;
                    }
                    if ($args) {
                        $newType = false;
                    }
                    if (!$api && $newType) {
                        // resolve request args.
                        $resolver = new ControllerResolver($sm, new ControllerNameParser(ServiceUtil::get('kernel')));
                        try {
                            $r = new \ReflectionClass($modfunc[0]);
                            if (!$r->hasMethod($modfunc[1])) {
                                // Method doesn't exist. Do some BC handling.
                                // First try to remove the 'Action' suffix.
                                $modfunc[1] = preg_replace('/(\w+)Action$/', '$1', $modfunc[1]);
                                if (!$r->hasMethod($modfunc[1])) {
                                    // Method still not found. Try to use the old 'main' method name.
                                    if ($modfunc[1] == 'index') {
                                        $modfunc[1] = $r->hasMethod('mainAction') ? 'mainAction' : 'main';
                                    }
                                }
                            }

                            if ($r->hasMethod($modfunc[1])) {
                                // Did we get a valid method? If so, resolve arguments!
                                $methodArgs = $resolver->getArguments($sm->get('request'), $modfunc);
                            } else {
                                // We still didn't get a valid method. Do not use argument resolving.
                                $newType = false;
                            }
                        } catch (\RuntimeException $e) {
                            // Something went wrong. Check if the method still uses the old non-Symfony $args array.
                            if ($modfunc[0] instanceof \Zikula_AbstractBase) {
                                $r = new \ReflectionMethod($modfunc[0], $modfunc[1]);
                                $parameters = $r->getParameters();
                                if (count($parameters) == 1) {
                                    $firstParameter = $parameters[0];
                                    if ($firstParameter->getName() == 'args') {
                                        // The method really uses the old $args parameter. In this case we can continue
                                        // using the old Controller call and don't have to throw an exception.
                                        $newType = false;
                                    }
                                }
                            }
                            if ($newType !== false) {
                                throw $e;
                            }
                        }
                    }

                    if ($modfunc[0] instanceof Zikula_AbstractController) {
                        $reflection = call_user_func([$modfunc[0], 'getReflection']);
                        $subclassOfReflection = new ReflectionClass($reflection->getParentClass());
                        if ($subclassOfReflection->hasMethod($modfunc[1])) {
                            // Don't allow front controller to access any public methods inside the controller's parents
                            throw new Zikula_Exception_NotFound();
                        }
                        $modfunc[0]->preDispatch();
                    }

                    if (!$api && $newType && isset($methodArgs)) {
                        $postExecuteEvent->setData(call_user_func_array($modfunc, $methodArgs));
                    } else {
                        $postExecuteEvent->setData(call_user_func($modfunc, $args));
                    }

                    if ($modfunc[0] instanceof Zikula_AbstractController) {
                        $modfunc[0]->postDispatch();
                    }
                } else {
                    $postExecuteEvent->setData($modfunc($args));
                }

                return $eventManager->dispatch('module_dispatch.postexecute', $postExecuteEvent)->getData();
            }

            // try to load plugin
            // This kind of eventhandler should
            // 1. Check $event['modfunc'] to see if it should run else exit silently.
            // 2. Do something like $result = {$event['modfunc']}({$event['args'});
            // 3. Save the result $event->setData($result).
            // 4. $event->setNotify().
            // return void
            // This event means that no $type was found
            $event = new \Zikula\Core\Event\GenericEvent(null, ['modfunc' => $modfunc, 'args' => $args, 'modinfo' => $modinfo, 'type' => $type, 'api' => $api], false);
            $eventManager->dispatch('module_dispatch.type_not_found', $event);

            if ($preExecuteEvent->isPropagationStopped()) {
                return $preExecuteEvent->getData();
            }

            return false;
        }

        // Issue not found exception for controller requests
        if (!System::isLegacyMode() && !$api) {
            throw new Zikula_Exception_NotFound(__f('The requested controller action %s_Controller_%s::%s() could not be found', [$modname, $type, $func]));
        }
    }

    /**
     * Run a module function.
     *
     * @param string $modname    The name of the module
     * @param string $type       The type of function to run
     * @param string $func       The specific function to run
     * @param array  $args       The arguments to pass to the function
     * @param string $instanceof Perform instanceof checking of target class
     *
     * @return mixed
     */
    public static function func($modname, $type = 'user', $func = 'main', $args = [], $instanceof = null)
    {
        @trigger_error('ModUtil class is deprecated, please use Symfony instead.', E_USER_DEPRECATED);

        return self::exec($modname, $type, $func, $args, false, $instanceof);
    }

    /**
     * Run an module API function.
     *
     * @param string $modname    The name of the module
     * @param string $type       The type of function to run
     * @param string $func       The specific function to run
     * @param array  $args       The arguments to pass to the function
     * @param string $instanceof Perform instanceof checking of target class
     *
     * @return mixed
     */
    public static function apiFunc($modname, $type = 'user', $func = 'main', $args = [], $instanceof = null)
    {
        @trigger_error('ModUtil class is deprecated, please use Symfony instead.', E_USER_DEPRECATED);

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

    private static function symfonyRoute($modname, $type, $func, $args, $ssl, $fragment, $fqurl, $forcelang)
    {
        /** @var \Symfony\Component\Routing\RouterInterface|\JMS\I18nRoutingBundle\Router\I18nRouter $router */
        $router = ServiceUtil::get('router');

        if (isset($args['lang'])) {
            $args['_locale'] = $args['lang'];
        }

        $routeNames = [strtolower($modname) . '_' . strtolower($type) . '_' . strtolower($func)];
        if ($func == 'index' || $func == 'main') {
            if ($func == 'index') {
                $routeNames[] = strtolower($modname) . '_' . strtolower($type) . '_main';
            } else {
                $routeNames[] = strtolower($modname) . '_' . strtolower($type) . '_index';
            }
        }

        if ($ssl) {
            $oldScheme = $router->getContext()->getScheme();
            $router->getContext()->setScheme('https');
        }

        // check for default route provided by capabilities array, unshift to beginning of search array.
        if ($func == 'index') {
            $modInfo = self::getInfoFromName($modname);
            if (isset($modInfo['capabilities'][$type]['route'])) {
                array_unshift($routeNames, $modInfo['capabilities'][$type]['route']);
            }
        }

        $found = false;
        foreach ($routeNames as $routeName) {
            try {
                $url = $router->generate($routeName, $args, ($fqurl) ? $router::ABSOLUTE_URL : $router::ABSOLUTE_PATH);
                $found = true;
                break;
            } catch (\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
            }
        }

        if ($ssl) {
            $router->getContext()->setScheme($oldScheme);
        }

        if (!$found) {
            return false;
        }

        if (isset($fragment)) {
            $url .= '#' . $fragment;
        }

        return $url;
    }

    /**
     * Generate a module function URL.
     *
     * If the module is non-API compliant (type 1) then
     * a) $func is ignored.
     * b) $type=admin will generate admin.php?module=... and $type=user will generate index.php?name=...
     *
     * @param string       $modname The name of the module
     * @param string       $type    The type of function to run
     * @param string       $func    The specific function to run
     * @param array        $args    The array of arguments to put on the URL
     * @param boolean|null $ssl     Set to constant null,true,false $ssl = true not $ssl = 'true'  null - leave the current status untouched,
     *                                     true - create a ssl url, false - create a non-ssl url
     * @param string         $fragment     The framgment to target within the URL
     * @param boolean|null   $fqurl        Fully Qualified URL. True to get full URL, eg for Redirect, else gets root-relative path unless SSL
     * @param boolean        $forcelongurl Force ModUtil::url to not create a short url even if the system is configured to do so
     * @param boolean|string $forcelang    Force the inclusion of the $forcelang or default system language in the generated url
     *
     * @return string Absolute URL for call
     */
    public static function url($modname, $type = null, $func = null, $args = [], $ssl = null, $fragment = null, $fqurl = null, $forcelongurl = false, $forcelang = false)
    {
        @trigger_error('ModUtil class is deprecated, please use Symfony routing instead.', E_USER_DEPRECATED);

        // define input, all numbers and booleans to strings
        $modname = isset($modname) ? ((string)$modname) : '';
        $modname = static::convertModuleName($modname);

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
            LogUtil::log(__('Warning! "Modules" module has been renamed to "ZikulaExtensionsModule".  Please update your ModUtil::url() or {modurl} calls with $module = "ZikulaExtensionsModule".'));
            $modname = 'ZikulaExtensionsModule';
        }

        // Try to generate the url using Symfony routing.
        $url = self::symfonyRoute($modname, $type, $func, $args, $ssl, $fragment, $fqurl, $forcelang);
        if ($url !== false) {
            return $url;
        }

        $request = \ServiceUtil::get('request');
        if ($request->attributes->has('_route_params')) {
            // If this attribute is set, a Symfony route has been matched. We need to generate full urls in that case.
            $fqurl = true;
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
            $language = ServiceUtil::get('request_stack')->getCurrentRequest()->getLocale();
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
            $url = self::apiFunc($modinfo['name'], 'user', 'encodeurl', ['modname' => $modname, 'type' => $type, 'func' => $func, 'args' => $args]);
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
                $pattern = '/^' . preg_quote($modinfo['url'], '/') . '\//';
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
            }

            foreach ($args as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $l => $w) {
                        if (is_numeric($w) || !empty($w)) {
                            // we suppress '', but allow 0 as value (see #193)
                            if (is_array($w)) {
                                foreach ($w as $m => $n) {
                                    if (is_numeric($n) || !empty($n)) {
                                        $n = strpos($n, '%') !== false ? $n : urlencode($n);
                                        $url .= "&$key" . "[$l][$m]=$n";
                                    }
                                }
                            } else {
                                $w = strpos($w, '%') !== false ? $w : urlencode($w);
                                $url .= "&$key" . "[$l]=$w";
                            }
                        }
                    }
                } elseif (is_numeric($value) || !empty($value)) {
                    // we suppress '', but allow 0 as value (see #193)
                    $value = strpos($value, '%') !== false ? $value : urlencode($value);
                    $url .= "&$key=$value";
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
     * @param string  $modname The name of the module
     * @param boolean $force   Force
     *
     * @return boolean True if the module is available, false if not
     */
    public static function available($modname = null, $force = false)
    {
        @trigger_error('ModUtil class is deprecated, please use Symfony instead.', E_USER_DEPRECATED);

        // define input, all numbers and booleans to strings
        $modname = (isset($modname) ? strtolower((string)$modname) : '');
        $modname = static::convertModuleName($modname);

        // validate
        if (!System::varValidate($modname, 'mod')) {
            return false;
        }

        if (!isset(self::$cache['modstate'])) {
            self::$cache['modstate'] = [];
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
                self::$cache['modstate'][$modname] == self::STATE_ACTIVE) || (preg_match('/^(zikulaextensionsmodule|zikulaadminmodule|zikulathememodule|zikulablockmodule|zikulagroupsmodule|zikulapermissionsmodule|zikulausersmodule)$/i', $modname) &&
                (isset(self::$cache['modstate'][$modname]) && (self::$cache['modstate'][$modname] == self::STATE_UPGRADED || self::$cache['modstate'][$modname] == self::STATE_INACTIVE)))) {
            self::$cache['modstate'][$modname] = self::STATE_ACTIVE;

            return true;
        }

        return false;
    }

    /**
     * Get name of current top-level module.
     *
     * @return string The name of the current top-level module, false if not in a module
     */
    public static function getName()
    {
        @trigger_error('ModUtil class is deprecated, please use Symfony instead.', E_USER_DEPRECATED);

        if (!isset(self::$cache['modgetname'])) {
            self::$cache['modgetname'] = FormUtil::getPassedValue('module', null, 'GETPOST', FILTER_SANITIZE_STRING);

            if (empty(self::$cache['modgetname'])) {
                self::$cache['modgetname'] = System::getVar('startpage');
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

            if (empty(self::$cache['modgetname'])) {
                self::$cache['modgetname'] = false;
            }
        }

        return self::$cache['modgetname'];
    }

    /**
     }
     */

    /**
     * Register all autoloaders for all modules in /modules containing a lib subdir
     * modules in /system should be Symfony structure based, so no manual autoloading needed
     *
     * @internal
     *
     * @return void
     */
    public static function registerAutoloaders()
    {
        @trigger_error('ModUtil class is deprecated, please use Symfony instead.', E_USER_DEPRECATED);

        $modules = self::getModsTable();
        unset($modules[0]);
        foreach ($modules as $module) {
            $path = "modules/$module[directory]/lib";
            if ($module['type'] == self::TYPE_MODULE) {
                if (is_dir($path)) {
                    ZLoader::addAutoloader($module['directory'], $path);
                } elseif (file_exists("modules/$module[directory]/Version.php")) {
                    ZLoader::addAutoloader($module['directory'], 'modules');
                }
            }
        }
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
     * @param string $modname Name of module to that you want the base directory of
     *
     * @return string The path from the root directory to the specified module
     */
    public static function getBaseDir($modname = '')
    {
        @trigger_error('ModUtil class is deprecated, please use Symfony instead.', E_USER_DEPRECATED);

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
     * @return array An array modules table
     */
    public static function getModsTable()
    {
        @trigger_error('ModUtil class is deprecated, please use ExtensionApi instead.', E_USER_DEPRECATED);

        if (!isset(self::$cache['modstable'])) {
            self::$cache['modstable'] = [];
        }

        if (!self::$cache['modstable'] || !\ServiceUtil::getManager()->getParameter('installed')) {
            // get entityManager
            $sm = ServiceUtil::getManager();
            $entityManager = $sm->get('doctrine.orm.default_entity_manager');

            // get all modules
            $modules = $entityManager->getRepository('Zikula\ExtensionsModule\Entity\ExtensionEntity')->findAll();

            foreach ($modules as $module) {
                $module = $module->toArray();
                if (!isset($module['url']) || empty($module['url'])) {
                    $module['url'] = strtolower($module['displayname']);
                }
                self::$cache['modstable'][$module['id']] = $module;
            }

            // add Core module (hack).
            self::$cache['modstable'][0] = [
                'id' => 0,
                'name' => 'zikula',
                'type' => self::TYPE_CORE,
                'directory' => '',
                'displayname' => 'Zikula Core v' . \Zikula_Core::VERSION_NUM,
                'version' => \Zikula_Core::VERSION_NUM,
                'state' => self::STATE_ACTIVE
            ];
        }

        return self::$cache['modstable'];
    }

    /**
     * Generic modules select function.
     *
     * Only modules in the module table are returned
     * which means that new/unscanned modules will not be returned.
     *
     * @param string $where The where clause to use for the select
     * @param string $sort  The sort to use
     *
     * @return array The resulting module object array
     */
    public static function getModules($where = [], $sort = 'displayname')
    {
        @trigger_error('ModUtil class is deprecated, please use ExtensionApi instead.', E_USER_DEPRECATED);

        // get entityManager
        $sm = ServiceUtil::getManager();
        $entityManager = $sm->get('doctrine.orm.default_entity_manager');

        // get all modules
        $modules = $entityManager->getRepository('Zikula\ExtensionsModule\Entity\ExtensionEntity')->findBy($where, [$sort => 'ASC']);

        return $modules;
    }

    /**
     * Return an array of modules in the specified state.
     *
     * Only modules in the module table are returned
     * which means that new/unscanned modules will not be returned.
     *
     * @param integer $state The module state (optional) (defaults = active state)
     * @param string  $sort  The sort to use
     *
     * @return array The resulting module object array
     */
    public static function getModulesByState($state = self::STATE_ACTIVE, $sort = 'displayname')
    {
        @trigger_error('ModUtil class is deprecated, please use ExtensionApi instead.', E_USER_DEPRECATED);

        $sm = ServiceUtil::getManager();
        $entityManager = $sm->get('doctrine.orm.default_entity_manager');
        $modules = $entityManager->getRepository('Zikula\ExtensionsModule\Entity\ExtensionEntity')->findBy(['state' => $state], [$sort => 'ASC']);

        return $modules;
    }

    /**
     * Initialize object oriented module.
     *
     * @param string $moduleName Module name
     *
     * @return boolean
     */
    public static function initOOModule($moduleName)
    {
        @trigger_error('ModUtil class is deprecated, please use Symfony instead.', E_USER_DEPRECATED);

        if (self::isInitialized($moduleName)) {
            return true;
        }

        $modinfo = self::getInfo(self::getIdFromName($moduleName));
        if (!$modinfo) {
            return false;
        }

        $modpath = ($modinfo['type'] == self::TYPE_SYSTEM) ? 'system' : 'modules';
        $osdir = DataUtil::formatForOS($modinfo['directory']);
        if (false === strpos($modinfo['directory'], '/')) {
            ZLoader::addAutoloader($moduleName, [
                realpath("$modpath"),
                realpath("$modpath/$osdir/lib")
            ]);
        }

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
     * @param string $moduleName Module name
     *
     * @return boolean
     */
    public static function isInitialized($moduleName)
    {
        @trigger_error('ModUtil class is deprecated, please use Symfony instead.', E_USER_DEPRECATED);

        return self::isOO($moduleName) && self::$ooModules[$moduleName]['initialized'];
    }

    /**
     * Checks whether a module is object oriented.
     *
     * @param string $moduleName Module name
     *
     * @deprecated
     *
     * @return boolean
     */
    public static function isOO($moduleName)
    {
        @trigger_error('ModUtil class is deprecated, please use Symfony instead.', E_USER_DEPRECATED);

        if (!isset(self::$ooModules[$moduleName])) {
            self::$ooModules[$moduleName] = [];
            self::$ooModules[$moduleName]['initialized'] = false;
            self::$ooModules[$moduleName]['oo'] = false;
            $modinfo = self::getInfo(self::getIdFromName($moduleName));
            if (!$modinfo) {
                return false;
            }

            self::$ooModules[$moduleName]['oo'] = true;
        }

        return self::$ooModules[$moduleName]['oo'];
    }

    /**
     * Determine the module base directory (system or modules).
     *
     * The purpose of this API is to decouple this calculation from the database,
     * since we ship core with fixed system modules, there is no need to calculate
     * this from the database over and over.
     *
     * @param string $moduleName Module name
     *
     * @return string Returns 'system' if system module, and 'modules' if not
     */
    public static function getModuleBaseDir($moduleName)
    {
        @trigger_error('ModUtil class is deprecated, please use Symfony instead.', E_USER_DEPRECATED);

        if (in_array(strtolower($moduleName), ['zikulaadminmodule', 'zikulablocksmodule', 'zikulacategoriesmodule', 'zikularoutesmodule', 'zikulaextensionsmodule', 'zikulagroupsmodule', 'zikulamailermodule', 'zikulamenumodule', 'zikulapermissionsmodule', 'zikulasearchmodule', 'zikulasecuritycentermodule', 'zikulasettingsmodule', 'zikulathememodule', 'zikulausersmodule', 'zikulazauthmodule'])) {
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
     * @param string $moduleName Module name
     *
     * @return string Returns module admin image path
     */
    public static function getModuleImagePath($moduleName)
    {
        @trigger_error('ModUtil class is deprecated, please use Symfony instead.', E_USER_DEPRECATED);

        if ($moduleName == '') {
            return false;
        }

        $modinfo = self::getInfoFromName($moduleName);
        $modpath = ($modinfo['type'] == self::TYPE_SYSTEM) ? 'system' : 'modules';

        $osmoddir = DataUtil::formatForOS($modinfo['directory']);
        $modulePath = self::getModuleRelativePath($modinfo['name']);

        $paths = [];
        if ($modulePath) {
            $paths[] = $modulePath . '/Resources/public/images/admin.png';
            $paths[] = $modulePath . '/Resources/public/images/admin.jpg';
            $paths[] = $modulePath . '/Resources/public/images/admin.gif';
        }

        $paths[] = $modpath . '/' . $osmoddir . '/images/admin.png';
        $paths[] = $modpath . '/' . $osmoddir . '/images/admin.jpg';
        $paths[] = $modpath . '/' . $osmoddir . '/images/admin.gif';
        $paths[] = 'system/AdminModule/Resources/public/images/default.gif';

        foreach ($paths as $path) {
            if (is_readable($path)) {
                break;
            }
        }

        return $path;
    }

    /**
     * Internal function to help migration from old module references.
     *
     * @todo remove in 1.4+ (drak)
     *
     * @param $name
     *
     * @return string
     *
     * @internal
     */
    public static function convertModuleName($name)
    {
        @trigger_error('ModUtil class is deprecated, please use Symfony instead.', E_USER_DEPRECATED);

        if (in_array($name, [
            'Blocks', 'Errors', 'Extensions', 'Groups', 'Mailer', 'Permissions',
            'PageLock', 'Search', 'SecurityCenter', 'Settings', 'Theme', 'Users',
            'Categories', 'Admin'
        ])) {
            $name = 'Zikula' . $name . 'Module';
        }

        return $name;
    }

    /**
     * Gets the object associated with a given module name
     *
     * @param string $moduleName
     * @param boolean $force = false Force load a module and add autoloaders
     *
     * @return null|\Zikula\Core\AbstractModule
     */
    public static function getModule($moduleName, $force = false)
    {
        @trigger_error('ModUtil class is deprecated, please use Symfony instead.', E_USER_DEPRECATED);

        /** @var $kernel Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel */
        $kernel = ServiceUtil::getManager()->get('kernel');
        try {
            return $kernel->getModule($moduleName);
        } catch (\InvalidArgumentException $e) {
        }

        if ($force) {
            $modInfo = self::getInfo(self::getIdFromName($moduleName));
            if (empty($modInfo)) {
                throw new \RuntimeException(__('Error! No such module exists.'));
            }
            $osDir = DataUtil::formatForOS($modInfo['directory']);
            $modPath = ($modInfo['type'] == self::TYPE_SYSTEM) ? "system" : "modules";
            $scanner = new Scanner();
            $scanner->scan(["$modPath/$osDir"], 1);
            $modules = $scanner->getModulesMetaData(true);
            /** @var $moduleMetaData \Zikula\Bundle\CoreBundle\Bundle\MetaData */
            $moduleMetaData = !empty($modules[$modInfo['name']]) ? $modules[$modInfo['name']] : null;
            if (null !== $moduleMetaData) {
                // moduleMetaData only exists for bundle-type modules
                $boot = new \Zikula\Bundle\CoreBundle\Bundle\Bootstrap();
                $boot->addAutoloaders($kernel, $moduleMetaData->getAutoload());
                if ($modInfo['type'] == self::TYPE_MODULE) {
                    if (is_dir("modules/$osDir/Resources/locale")) {
                        ZLanguage::bindModuleDomain($modInfo['name']);
                    }
                }
                $moduleClass = $moduleMetaData->getClass();

                return new $moduleClass();
            }
        }

        return null;
    }

    /**
     * Gets the file system path of the module relative to site root
     *
     * @param $modName
     *
     * @return bool|mixed False or path
     */
    public static function getModuleRelativePath($modName)
    {
        @trigger_error('ModUtil class is deprecated, please use Symfony instead.', E_USER_DEPRECATED);

        $module = self::getModule($modName);
        $path = false;
        if ($module) {
            $path = substr($module->getPath(), strpos($module->getPath(), self::getModuleBaseDir($modName)), strlen($module->getPath()));
            $path = str_replace('\\', '/', $path);
        }

        return $path;
    }

    /**
     * Checks if a module is a core (i.e. located in system/) module
     *
     * @param $module
     *
     * @return bool|mixed False or path
     */
    public static function isCore($module)
    {
        @trigger_error('ModUtil class is deprecated, please use Symfony instead.', E_USER_DEPRECATED);

        return ('system' === self::getModuleBaseDir($module)) ? true : false;
    }
}
