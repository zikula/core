<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Zikula_Workflow_Util Class.
 *
 * From a developers standpoint, we only use this class to address workflows
 * as the rest is for internal use by the workflow engine.
 *
 * @deprecated
 */
class Zikula_Workflow_Util
{
    /**
     * Load xml workflow.
     *
     * @param string $schema Name of workflow scheme
     * @param string $module Name of module
     *
     * @return mixed string of XML, or false
     */
    public static function loadSchema($schema = 'standard', $module = null)
    {
        static $workflows;

        if (!isset($workflows)) {
            $workflows = [];
        }

        // if no module specified, default to calling module
        if (empty($module)) {
            $module = ModUtil::getName();
        }

        // workflow caching
        if (isset($workflows[$module][$schema])) {
            return $workflows[$module][$schema];
        }

        // Get module info
        $modinfo = ModUtil::getInfoFromName($module);
        if (!$modinfo) {
            throw new \Exception(__f('%1$s: The specified module [%2$s] does not exist.', ['Zikula_Workflow_Util', $module]));
        }

        $path = self::_findpath("$schema.xml", $module);
        if ($path) {
            $workflowXML = file_get_contents($path);
        } else {
            throw new \Exception(__f('%1$s: Unable to find the workflow file [%2$s].', ['Zikula_Workflow_Util', $path]));
        }

        // instanciate Workflow Parser
        $parser = new Zikula_Workflow_Parser();

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
     * Find the path of the file by searching overrides and the module location.
     *
     * @param string $file   Name of file to find (can include relative path)
     * @param string $module Module name
     *
     * @return mixed string of path or bool false
     */
    public static function _findpath($file, $module = null)
    {
        // if no module specified, default to calling module
        if (empty($module)) {
            $module = ModUtil::getName();
        }

        // Get module info
        $modinfo = ModUtil::getInfoFromName($module);
        if (!$modinfo) {
            throw new \Exception(__f('%1$s: The specified module [%2$s] does not exist.', ['Zikula_Workflow_Util', $module]));
        }

        $moduledir = $modinfo['directory'];

        // determine which folder to look in (system or modules)
        if ($modinfo['type'] == ModUtil::TYPE_SYSTEM) {
            // system module
            $modulepath = "system/$moduledir";
        } elseif ($modinfo['type'] == ModUtil::TYPE_MODULE) {
            // non system module
            $modulepath = "modules/$moduledir";
        } else {
            throw new \Exception(__f('%s: Unsupported module type.', 'Zikula_Workflow_Util'));
        }

        // ensure module is active
        if (!$modinfo['state'] == 3) {
            throw new \Exception(__f('%1$s: The module [%2$s] is not active.', ['Zikula_Workflow_Util', $module]));
        }

        $themedir = ThemeUtil::getInfo(ThemeUtil::getIDFromName(UserUtil::getTheme()));
        $themedir = $themedir['directory'];
        $themepath = DataUtil::formatForOS("themes/$themedir/workflows/$moduledir/$file");
        $configpath = DataUtil::formatForOS("config/workflows/$moduledir/$file");
        $modulepath = DataUtil::formatForOS("$modulepath/workflows/$file");

        // find the file in themes or config (for overrides), else module dir
        if (is_readable($themepath)) {
            return $themepath;
        } elseif (is_readable($configpath)) {
            return $configpath;
        } elseif (is_readable($modulepath)) {
            return $modulepath;
        }

        return false;
    }

    /**
     * Execute action.
     *
     * @param string $schema Name of workflow schema
     * @param array  &$obj     Data object
     * @param string $actionID Action to perform
     * @param string $table    Table where data will be stored (default = null)
     * @param string $module   Name of module (defaults calling module)
     * @param string $idcolumn ID column of table
     *
     * @return mixed
     */
    public static function executeAction($schema, &$obj, $actionID, $table = null, $module = null, $idcolumn = 'id')
    {
        if (!isset($obj)) {
            throw new \Exception(__f('%1$s: %2$s not set.', ['Zikula_Workflow_Util', 'obj']));
        }

        if (!is_array($obj) && !is_object($obj)) {
            throw new \Exception(__f('%1$s: %2$s must be an array or an object.', ['Zikula_Workflow_Util', 'obj']));
        }

        if (empty($schema)) {
            throw new \Exception(__f('%1$s: %2$s needs to be named', ['Zikula_Workflow_Util', 'schema']));
        }

        if (is_null($module)) {
            // default to calling module
            $module = ModUtil::getName();
        }

        $stateID = self::getWorkflowState($obj, $table, $idcolumn, $module);
        if (!$stateID) {
            $stateID = 'initial';
        }

        // instanciate workflow
        $workflow = new Zikula_Workflow($schema, $module);

        return $workflow->executeAction($actionID, $obj, $stateID);
    }

     /**
      * Delete workflows for module (used module uninstall time).
      *
      * @param string $module Module name
      *
      * @return boolean
      */
     public static function deleteWorkflowsForModule($module)
     {
         $theModule = isset($module) ? $module : ModUtil::getName();

        //This is a static function, so we have to use ServiceUtil to get the entity manager
        $em = ServiceUtil::getManager()->get('doctrine.entitymanager');

        //create the dql query.
        $query = $em->createQueryBuilder()
                    ->delete('Zikula\Core\Doctrine\Entity\WorkflowEntity', 'w')
                    ->where('w.module = :module')
                    ->setParameter('module', $theModule)
                    ->getQuery();

         $result = $query->execute();

         return $result;
     }

    /**
     * Delete a workflow and associated data.
     *
     * @param array $obj Data object
     *
     * @return boolean
     */
    public static function deleteWorkflow($obj)
    {
        $workflow = $obj['__WORKFLOW__'];
        $idColumn = $workflow['obj_idcolumn'];

        //This is a static function, so we have to use ServiceUtil to get the entity manager
        $em = ServiceUtil::getManager()->get('doctrine.entitymanager');

        //create the dql query.
        $query = $em->createQueryBuilder()
                    ->delete('Zikula\Core\Doctrine\Entity\WorkflowEntity', 'w')
                    ->where('w.objIdcolumn = :idColumn')
                    ->setParameter('idColumn', $idColumn)
                    ->getQuery();

        $result = $query->execute();

        return $result;
    }

    /**
     * Get actions by state.
     *
     * Returns allowed action data for given state.
     *
     * @param string $schemaName Schema name
     * @param string $module     Module name
     * @param string $state      State name, default = 'initial'
     * @param array  $obj        Data object
     *
     * @return mixed Array $action.id => $action or bool false
     */
    public static function getActionsByState($schemaName, $module = null, $state = 'initial', $obj = [])
    {
        if (!isset($module)) {
            $module = ModUtil::getName();
        }

        // load up schema
        $schema = self::loadSchema($schemaName, $module);
        if (!$schema) {
            return false;
        }

        $actions = $schema['actions'][$state];
        $allowedActions = [];
        foreach ($actions as $action) {
            if (self::permissionCheck($module, $schemaName, $obj, $action['permission'], $action['id'])) {
                $allowedActions[$action['id']] = $action;
            }
        }

        return $allowedActions;
    }

    /**
     * Get Actions Titles by State.
     *
     * Returns allowed action ids and titles only, for given state.
     *
     * @param string $schemaName Schema name
     * @param string $module     Module name
     * @param string $state      State, default = 'initial'
     * @param array  $obj        Array object
     *
     * @return mixed Array $action.id => $action.title or bool false
     */
    public static function getActionTitlesByState($schemaName, $module = null, $state = 'initial', $obj = [])
    {
        $allowedActions = self::getActionsByState($schemaName, $module, $state, $obj);

        if ($allowedActions) {
            foreach (array_keys($allowedActions) as $id) {
                $allowedActions[$id] = $allowedActions[$id]['title'];
            }
        }

        return $allowedActions;
    }

    /**
     * getActionsByStateArray.
     *
     * Returns allowed action data for given state.
     *
     * @param string $schemaName Schema name
     * @param string $module     Module name
     * @param string $state      State, default = 'initial'
     * @param array  $obj        Array object
     *
     * @deprecated 1.3.0
     * @return mixed Array or bool false
     */
    public static function getActionsByStateArray($schemaName, $module = null, $state = 'initial', $obj = [])
    {
        return self::getActionsByState($schemaName, $module, $state, $obj);
    }

    /**
     * Get possible actions for a given item of data in it's current workflow state.
     *
     * @param array  &$obj     Array object
     * @param string $dbTable  Database table
     * @param string $idcolumn Id field, default = 'id'
     * @param string $module   Module name (defaults to current module)
     *
     * @return mixed Array of actions or bool false
     */
    public static function getActionsForObject(&$obj, $dbTable, $idcolumn = 'id', $module = null)
    {
        if (!is_array($obj) && !is_object($obj)) {
            throw new \Exception(__f('%1$s: %2$s is not an array nor an object.', ['Zikula_Workflow_Util::getActionsForObject', 'object']));
        }

        if (!isset($dbTable)) {
            throw new \Exception(__f('%1$s: %2$s is specified.', ['Zikula_Workflow_Util::getActionsForObject', 'dbTable']));
        }

        if (empty($module)) {
            $module = ModUtil::getName();
        }

        if (!isset($obj['__WORKFLOW__'])) {
            if (!self::getWorkflowForObject($obj, $dbTable, $idcolumn, $module)) {
                return false;
            }
        }

        $workflow = $obj['__WORKFLOW__'];

        return self::getActionsByState($workflow['schemaname'], $workflow['module'], $workflow['state'], $obj);
    }

    /**
     * Load workflow for object.
     *
     * Will attach array '__WORKFLOW__' to the object.
     *
     * @param array  &$obj     Array object
     * @param string $dbTable  Database table
     * @param string $idcolumn Id field, default = 'id'
     * @param string $module   Module name (defaults to current module)
     *
     * @return boolean
     */
    public static function getWorkflowForObject(&$obj, $dbTable, $idcolumn = 'id', $module = null)
    {
        if (empty($module)) {
            $module = ModUtil::getName();
        }

        if (!isset($obj) || (!is_array($obj) && !is_object($obj))) {
            throw new \Exception(__f('%1$s: %2$s is not an array nor an object.', ['Zikula_Workflow_Util::getWorkflowForObject', 'object']));
        }

        if (!isset($dbTable)) {
            throw new \Exception(__f('%1$s: %2$s is not specified.', ['Zikula_Workflow_Util::getWorkflowForObject', 'dbTable']));
        }

        $workflow = false;

        if (!empty($obj[$idcolumn])) {
            //This is a static function, so we have to user ServiceUtil to get the manager
            $em = ServiceUtil::getManager()->get('doctrine.entitymanager');

            //build the query and execute
            $query = $em->createQueryBuilder()
                         ->select('w')
                         ->from('Zikula\Core\Doctrine\Entity\WorkflowEntity', 'w')
                         ->where('w.module = :module')
                         ->andWhere('w.objIdcolumn = :idcolumn')
                         ->andWhere('w.objTable = :dbtable')
                         ->andWhere('w.objId = :id')
                         ->setParameter('module', $module)
                         ->setParameter('idcolumn', $idcolumn)
                         ->setParameter('dbtable', $dbTable)
                         ->setParameter('id', $obj[$idcolumn])
                         ->getQuery();

            $workflow = $query->getArrayResult();
            if ($workflow) {
                $workflow = $workflow[0];
                $renameKey = function (&$array, $old, $new) {
                    $array[$new] = $array[$old];
                    unset($array[$old]);
                };
                $renameKey($workflow, 'objTable', 'obj_table');
                $renameKey($workflow, 'objId', 'obj_id');
                $renameKey($workflow, 'objIdcolumn', 'obj_idcolumn');
            }
        }

        if (!$workflow) {
            $workflow = [
                'state'        => 'initial',
                'obj_table'    => $dbTable,
                'obj_idcolumn' => $idcolumn,
                'obj_id'       => $obj[$idcolumn]
            ];
        }

        // attach workflow to object
         if ($obj instanceof Doctrine_Record) {
             $obj->mapValue('__WORKFLOW__', $workflow);
         } else {
             $obj['__WORKFLOW__'] = $workflow;
         }

        return true;
    }

    /**
     * get workflow state of object
     *
     * @param array  &$obj     Array object
     * @param string $table    Table name
     * @param string $idcolumn Id field, default = 'id'
     * @param string $module   Module name (defaults to current module)
     *
     * @return mixed String workflow state name or false
     */
    public static function getWorkflowState(&$obj, $table, $idcolumn = 'id', $module = null)
    {
        if (empty($module)) {
            $module = ModUtil::getName();
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
     * @param string  $module    Module name
     * @param string  $schema    Schema name
     * @param array   $obj       Array object
     * @param string  $permLevel Permission level
     * @param integer $actionId  Action Id
     *
     * @return boolean
     */
    public static function permissionCheck($module, $schema, $obj = [], $permLevel = 'overview', $actionId = null)
    {
        // translate permission to something meaningful
        $permLevel = self::translatePermission($permLevel);

        // test conversion worked
        if (!$permLevel) {
            return false;
        }

        // get current user
        $currentUser = UserUtil::getVar('uid');
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
            throw new \Exception(__f("Permission check file [%s] does not exist.", "function.{$schema}_permissioncheck.php"));
        }

        // load file and test if function exists
        include_once $path;
        if (!function_exists($function)) {
            throw new \Exception(__f("Permission check function [%s] not defined.", $function));
        }

        // function must be loaded so now we can execute the function
        return $function($obj, $permLevel, $currentUser, $actionId);
    }

    /**
     * translates workflow permission to pn permission define
     *
     * @param string $permission Permission string
     *
     * @return mixed Permission constant or false
     */
    public static function translatePermission($permission)
    {
        switch (strtolower($permission)) {
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
