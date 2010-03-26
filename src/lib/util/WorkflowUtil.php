<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * WorkflowUtil Class
 * From a developers standpoint, we only use this class to address workflows
 * as the rest is for internal use by the workflow engine.
 *
 * @package Zikula_Core
 * @subpackage WorkflowUtil
 */
class WorkflowUtil
{
    /**
     * load xml workflow
     *
     * @param string $schema name of workflow scheme
     * @param string $module name of module
     *
     * @return mixed string of XML, or false
     */
    public static function loadSchema($schema = 'standard', $module = null)
    {
        static $workflows;

        if (!isset($workflows)) {
            $workflows = array();
        }

        // if no module specified, default to calling module
        if (empty($module)) {
            $module = pnModGetName();
        }

        // workflow caching
        if (isset($workflows[$module][$schema])) {
            return $workflows[$module][$schema];
        }

        // Get module info
        $modinfo = pnModGetInfo(pnModGetIDFromName($module));
        if (!$modinfo) {
            return pn_exit(__f('%1$s: The specified module [%2$s] does not exist.', array('WorkflowUtil', $module)));
        }

        $path = self::_findpath("$schema.xml", $module);
        if ($path) {
            $workflowXML = file_get_contents($path);
        } else {
            return pn_exit(__f('%1$s: Unable to find the workflow file [%2$s].', array('WorkflowUtil', $path)));
        }

        // instanciate Workflow Parser
        $parser = new ZWorkflowParser();

        // parse workflow and return workflow object
        $workflowSchema = $parser->parse($workflowXML, $schema, $module);

        // destroy parser
        unset($parser);

        // cache workflow
        $workflows[$module][$schema] = $workflowSchema;

        // return workflow object
        return $workflows[$module][$schema];
    }

    /**
     * Find the path of the file by searching overrides and the module location
     *
     * @access private
     * @param string $file name of file to find (can include relative path)
     * @param string $module
     * @return mixed string of path or bool false
     */
    public static function _findpath($file, $module = null)
    {
        // if no module specified, default to calling module
        if (empty($module)) {
            $module = pnModGetName();
        }

        // Get module info
        $modinfo = pnModGetInfo(pnModGetIDFromName($module));
        if (!$modinfo) {
            return pn_exit(__f('%1$s: The specified module [%2$s] does not exist.', array('WorkflowUtil', $module)));
        }

        $moduledir = $modinfo['directory'];

        // determine which folder to look in (system or modules)
        if ($modinfo['type'] == 3) { // system module
            $modulepath = "system/$moduledir";
        } else if ($modinfo['type'] == 2) { // non system module
            $modulepath = "modules/$moduledir";
        } else {
            return pn_exit(__f('%s: Unsupported module type.', 'WorkflowUtil'));
        }

        // ensure module is active
        if (!$modinfo['state'] == 3) {
            return pn_exit(__f('%1$s: The module [%2$s] is not active.', array('WorkflowUtil', $module)));
        }

        $themedir = ThemeUtil::getInfo(ThemeUtil::getIDFromName(pnUserGetTheme()));
        $themepath = DataUtil::formatForOS("themes/$themedir/workflows/$moduledir/$file");
        $configpath = DataUtil::formatForOS("config/workflows/$moduledir/$file");
        $modulepath = DataUtil::formatForOS("$modulepath/workflows/$file");

        // find the file in themes or config (for overrides), else module dir
        if (is_readable($themepath)) {
            return $themepath;
        } else if (is_readable($configpath)) {
            return $configpath;
        } else if (is_readable($modulepath)) {
            return $modulepath;
        } else {
            return false;
        }
    }

    /**
     * Execute action
     *
     * @param string $schema name of workflow schema
     * @param array $obj data object
     * @param string $actionID action to perform
     * @param string $table table where data will be stored (default = null)
     * @param string $module name of module (defaults calling module)
     * @param int $id ID column of table
     * @return mixed
     */
    public static function executeAction($schema, &$obj, $actionID, $table = null, $module = null, $idcolumn = 'id')
    {
        if (!isset($obj)) {
            return pn_exit(__f('%s: $obj not set.', 'WorkflowUtil'));
        }

        if (!is_array($obj)) {
            return pn_exit(__f('%s: $obj must be an array.', 'WorkflowUtil'));
        }

        if (empty($schema)) {
            return pn_exit(__f('%s: $schema needs to be named', 'WorkflowUtil'));
        }

        if (is_null($module)) {
            // default to calling module
            $module = pnModGetName();
        }

        $stateID = self::getWorkflowState($obj, $table, $idcolumn, $module);
        if (!$stateID) {
            $stateID = 'initial';
        }

        // instanciate workflow
        $workflow = new ZWorkflow($schema, $module);

        return $workflow->executeAction($actionID, $obj, $stateID);
    }

    /**
     * delete workflows for module (used module uninstall time)
     *
     * @param string $module
     * @return bool
     */
    public static function deleteWorkflowsForModule($module)
    {
        if (!isset($module)) {
            $module = pnModGetName();
        }

        if (!pnModDBInfoLoad('Workflow')) {
            return false;
        }

        // this is a cheat to delete all items in table with value $module
        return (bool) DBUtil::deleteObjectByID('workflows', $module, 'module');
    }

    /**
     * delete a workflow and associated data
     *
     * @param array $obj
     * @return bool
     */
    public static function deleteWorkflow($obj)
    {
        $workflow = $obj['__WORKFLOW__'];
        $idcolumn = $workflow['obj_idcolumn'];
        if (!DBUtil::deleteObjectByID($workflow['obj_table'], $obj[$idcolumn], $idcolumn)) {
            return false;
        }

        return (bool) DBUtil::deleteObjectByID('workflows', $workflow['id']);
    }

    /**
     * get Actions by State
     *
     * Returns allowed action ids for given state
     *
     * @param string $schemaName
     * @param string $module
     * @param string $state default = 'initial'
     * @param array $obj
     * @return mixed array or bool false
     */
    public static function getActionsByState($schemaName, $module = null, $state = 'initial', $obj = array())
    {
        if (!isset($module)) {
            $module = pnModGetName();
        }

        // load up schema
        $schema = self::loadSchema($schemaName, $module);
        if (!$schema) {
            return false;
        }

        $actions = $schema['actions'][$state];
        $allowedActions = array();
        foreach ($actions as $action) {
            if (self::permissionCheck($module, $schemaName, $obj, $action['permission'], $action['id'])) {
                $allowedActions[$action['id']] = $action['id'];
            }
        }

        return $allowedActions;
    }

    /**
     * getActionsByStateArray
     *
     * Returns allowed action array for given state
     *
     * @param string $schemaName
     * @param string $module
     * @param string $state default = 'initial'
     * @param array $obj
     * @return mixed array or bool false
     */
    public static function getActionsByStateArray($schemaName, $module = null, $state = 'initial', $obj = array())
    {
        if (!isset($module)) {
            $module = pnModGetName();
        }

        // load up schema
        $schema = self::loadSchema($schemaName, $module);
        if (!$schema) {
            return false;
        }

        $actions = $schema['actions'][$state];
        $allowedActions = array();
        foreach ($actions as $action) {
            if (self::permissionCheck($module, $schemaName, $obj, $action['permission'], $action['id'])) {
                $allowedActions[$action['id']] = $action;
            }
        }

        return $allowedActions;
    }

    /**
     * get possible actions for a given item of data in it's current workflow state
     *
     * @param array $obj
     * @param string $dbTable
     * @param mixed $idcolumn id field default = 'id'
     * @param string $module module name (defaults to current module)
     *
     * @return mixed array of actions or bool false
     */
    public static function getActionsForObject(&$obj, $dbTable, $idcolumn = 'id', $module = null)
    {
        if (!is_array($obj)) {
            return pn_exit(__f('%1$s: %2$s is not an array.', array('WorkflowUtil::getActionsForObject', 'object')));
        }

        if (!isset($dbTable)) {
            return pn_exit(__f('%1$s: %2$s is specified.', array('WorkflowUtil::getActionsForObject', 'dbTable')));
        }

        if (empty($module)) {
            $module = pnModGetName();
        }

        if (!self::getWorkflowForObject($obj, $dbTable, $idcolumn, $module)) {
            return false;
        }

        $workflow = $obj['__WORKFLOW__'];
        return self::getActionsByState($workflow['schemaname'], $workflow['module'], $workflow['state'], $obj);
    }

    /**
     * Load workflow for object
     * will attach array '__WORKFLOW__' to the object
     *
     * @param array $obj
     * @param string $dbTable name of table where object is or will be stored
     * @param string $id name of ID column of object
     * @param string $module module name (defaults to current module)
     * @return bool
     */
    public static function getWorkflowForObject(&$obj, $dbTable, $idcolumn = 'id', $module = null)
    {
        if (empty($module)) {
            $module = pnModGetName();
        }

        if (!isset($obj) || !is_array($obj)) {
            return pn_exit(__f('%1$s: %2$s is not an array.', array('WorkflowUtil::getWorkflowForObject', 'object')));
        }

        if (!isset($dbTable)) {
            return pn_exit(__f('%1$s: %2$s is specified.', array('WorkflowUtil::getWorkflowForObject', 'dbTable')));
        }

        // get workflow data from DB
        if (!pnModDBInfoLoad('Workflow')) {
            return false;
        }

        $pntables = pnDBGetTables();
        $workflows_column = $pntables['workflows_column'];
        $where = "WHERE $workflows_column[module]='" . DataUtil::formatForStore($module) . "'
                    AND $workflows_column[obj_table]='" . DataUtil::formatForStore($dbTable) . "'
                    AND $workflows_column[obj_idcolumn]='" . DataUtil::formatForStore($idcolumn) . "'
                    AND $workflows_column[obj_id]='" . DataUtil::formatForStore($obj[$idcolumn]) . "'";
        $workflow = DBUtil::selectObject('workflows', $where);

        if (!$workflow) {
            $workflow = array('state' => 'initial', 'obj_table' => $dbTable, 'obj_idcolumn' => $idcolumn, 'obj_id' => $obj[$idcolumn]);
        }

        // attach workflow to object
        $obj['__WORKFLOW__'] = $workflow;
        return true;
    }

    /**
     * get workflow state of object
     *
     * @param array $obj
     * @param string $table
     * @param string $idcolumn name of ID column
     * @param string $module module name (defaults to current module)
     *
     * @return mixed string workflow state name or false
     */
    public static function getWorkflowState(&$obj, $table, $idcolumn = 'id', $module = null)
    {
        if (empty($module)) {
            $module = pnModGetName();
        }

        if (!isset($obj['__WORKFLOW__'])) {
            if (!self::getWorkflowForObject($obj, $table, $idcolumn, $module)) {
                return false;
            }
        }

        $workflow = $obj['__WORKFLOW__'];
        return $workflow['state'];
    }

    /**
     * Check permission of action
     *
     * @param string $module
     * @param string $schema
     * @param array $obj
     * @param int $permLevel
     * @param int $actionId
     * @return bool
     */
    public static function permissionCheck($module, $schema, $obj = array(), $permLevel, $actionId = null)
    {
        // translate permission to something meaningful
        $permLevel = self::translatePermission($permLevel);

        // test conversion worked
        if (!$permLevel) {
            return false;
        }

        // get current user
        $currentUser = pnUserGetVar('uid');
        // no user then assume anon
        if (empty($currentUser)) {
            $currentUser = -1;
        }

        $function = "{$module}_workflow_{$schema}_permissioncheck";
        if (function_exists($function)) {
            // function already exists
            return $function($obj, $permLevel, $currentUser, $actionId);
        }

        // test operation file exists
        $path = self::_findpath("function.{$schema}_permissioncheck.php", $module);
        if (!$path) {
            return pn_exit(__f("Permission check file [%s] does not exist.", "function.{$schema}_permissioncheck.php"));
        }

        // load file and test if function exists
        Loader::includeOnce($path);
        if (!function_exists($function)) {
            return pn_exit(__f("Permission check function [%s] not defined.", $function));
        }

        // function must be loaded so now we can execute the function
        return $function($obj, $permLevel, $currentUser, $actionId);
    }

    /**
     * translates workflow permission to pn permission define
     *
     * @param string $permission
     * @return mixed int or false
     */
    public static function translatePermission($permission)
    {
        $permission = strtolower($permission);
        switch ($permission) {
            case 'invalid':
                return ACCESS_INVALID;
            case 'overview':
                return ACCESS_OVERVIEW;
            case 'read':
                return ACCESS_READ;
            case 'comment':
                return ACCESS_COMMENT;
            case 'moderate':
                return ACCESS_MODERATE;
            case 'moderator':
                return ACCESS_MODERATE;
            case 'edit':
                return ACCESS_EDIT;
            case 'editor':
                return ACCESS_EDIT;
            case 'add':
                return ACCESS_ADD;
            case 'author':
                return ACCESS_ADD;
            case 'delete':
                return ACCESS_DELETE;
            case 'admin':
                return ACCESS_ADMIN;
            default:
                return false;
        }
    }
}
