<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Zikula_Workflow class.
 */
class Zikula_Workflow
{
    /**
     * Module name.
     *
     * @var string
     */
    protected $module;

    /**
     * Workflow Id.
     *
     * @var integer
     */
    protected $id;

    /**
     * Workflow title.
     *
     * @var string
     */
    protected $title;

    /**
     * Workflow description.
     *
     * @var string
     */
    protected $description;

    /**
     * State map.
     *
     * @var array
     */
    protected $stateMap;

    /**
     * Action map.
     *
     * @var array
     */
    protected $actionMap;

    /**
     * Workflow data.
     *
     * @var array
     */
    protected $workflowData;

    /**
     * Constructor.
     *
     * @param string $schema Schema.
     * @param string $module Module name.
     */
    public function __construct($schema, $module)
    {
        // load workflow schema
        $schema = WorkflowUtil::loadSchema($schema, $module);

        $this->id = $schema['workflow']['id'];
        $this->title = $schema['workflow']['title'];
        $this->description = $schema['workflow']['description'];
        $this->module = $module;
        $this->actionMap = $schema['actions'];
        $this->stateMap = $schema['states'];
        $this->workflowData = null;
    }

    /**
     * Register workflow by $metaId.
     *
     * @param array  &$obj    Data object.
     * @param string $stateID State Id.
     *
     * @return boolean
     */
    public function registerWorkflow(&$obj, $stateID = null)
    {
        $workflowData = $obj['__WORKFLOW__'];
        $idcolumn = $workflowData['obj_idcolumn'];
        $insertObj = array('obj_table' => $workflowData['obj_table'], 'obj_idcolumn' => $workflowData['obj_idcolumn'], 'obj_id' => $obj[$idcolumn], 'module' => $this->getModule(), 'schemaname' => $this->id, 'state' => $stateID);

        if (!DBUtil::insertObject($insertObj, 'workflows')) {
            return false;
        }

        $this->workflowData = $insertObj;
        $obj['__WORKFLOW__'] = $insertObj;
        return true;
    }

    /**
     * Update workflow state.
     *
     * @param string $stateID State Id.
     * @param string $debug   Debug string.
     *
     * @return boolean
     */
    public function updateWorkflowState($stateID, $debug = null)
    {
        $obj = array('id' => $this->workflowData['id'], 'state' => $stateID);

        if (isset($debug)) {
            $obj['debug'] = $debug;
        }

        return (bool)DBUtil::updateObject($obj, 'workflows');
    }

    /**
     * Execute workflow action.
     *
     * @param string $actionID Action Id.
     * @param array  &$obj     Data object.
     * @param string $stateID  State Id.
     *
     * @return mixed Array or false.
     */
    public function executeAction($actionID, &$obj, $stateID = 'initial')
    {
        // check if state exists
        if (!isset($this->actionMap[$stateID])) {
            return z_exit("STATE: $stateID not found");
        }

        // check the action exists for given state
        if (!isset($this->actionMap[$stateID][$actionID])) {
            return z_exit(__f('Action: %1$s not available in this State: %2$s', array($actionID, $stateID)));
        }

        $action = $this->actionMap[$stateID][$actionID];

        // permission check
        if (!WorkflowUtil::permissionCheck($this->module, $this->id, $obj, $action['permission'])) {
            return z_exit(__f('No permission to execute action: %s [permission]', $action));
        }

        // commit workflow to object
        $this->workflowData = $obj['__WORKFLOW__'];

        // get operations
        $operations = $action['operations'];
        $nextState = (isset($action['nextState']) ? $action['nextState'] : $stateID);

        foreach ($operations as $operation) {
            $result[$operation['name']] = $this->executeOperation($operation, $obj, $nextState);
            if (!$result[$operation['name']]) {
                // if an operation fails here, do not process further and return false
                return false;
            }
        }

        // test if state needs to be updated
        if ($nextState == $stateID) {
            return $result;
        }

        // if this is an initial object then we need to register with the DB
        if ($stateID == 'initial') {
            $this->registerWorkflow($obj, $stateID);
        }

        // change the workflow state
        if (!$this->updateWorkflowState($nextState)) {
            return false;
        }

        // return result of all operations (possibly okay to just return true here)
        return $result;
    }

    /**
     * Execute workflow operation within action.
     *
     * @param string $operation Operation name.
     * @param array  &$obj      Data object.
     * @param string $nextState Next state.
     *
     * @return mixed|false
     */
    public function executeOperation($operation, &$obj, $nextState)
    {
        $operationName = $operation['name'];
        $operationParams = $operation['parameters'];

        // test operation file exists
        $path = WorkflowUtil::_findpath("operations/function.{$operationName}.php", $this->module);
        if (!$path) {
            return z_exit(__f('Operation file [%s] does not exist', $operationName));
        }

        // load file and test if function exists
        include_once $path;
        $function = "{$this->module}_operation_{$operationName}";
        if (!function_exists($function)) {
            return z_exit(__f('Operation function [%s] is not defined', $function));
        }

        // execute operation and return result
        return $function($obj, $operationParams);
    }

    /**
     * get workflow ID
     *
     * @return string workflow schema name
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * get workflow title
     *
     * @return string title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * get workflow description
     *
     * @return string description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * get workflow Module
     *
     * @return string module name
     */
    public function getModule()
    {
        return $this->module;
    }
}
