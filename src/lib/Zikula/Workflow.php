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
        $schema = Zikula_Workflow_Util::loadSchema($schema, $module);

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
        $idcolumn = $obj['__WORKFLOW__']['obj_idcolumn'];

        $insertObj = array('obj_table'    => $obj['__WORKFLOW__']['obj_table'],
                           'obj_idcolumn' => $obj['__WORKFLOW__']['obj_idcolumn'],
                           'obj_id'       => $obj[$idcolumn],
                           'module'       => $this->getModule(),
                           'schemaname'   => $this->id,
                           'state'        => $stateID);

        if (!DBUtil::insertObject($insertObj, 'workflows')) {
            return false;
        }

        $this->workflowData = $insertObj;
        if ($obj instanceof Doctrine_Record) {
            $obj->mapValue('__WORKFLOW__', $insertObj);
        } else {
            $obj['__WORKFLOW__'] = $insertObj;
        }

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
        $obj = array('id'    => $this->workflowData['id'],
                     'state' => $stateID);

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
        if (!Zikula_Workflow_Util::permissionCheck($this->module, $this->id, $obj, $action['permission'])) {
            return z_exit(__f('No permission to execute action: %s [permission]', $action));
        }

        // commit workflow to object
        $this->workflowData = $obj['__WORKFLOW__'];

        // define the next state to be passed to the operations
        $nextState = (isset($action['nextState']) ? $action['nextState'] : $stateID);

        // process the action operations
        $result = array();
        foreach ($action['operations'] as $operation) {
            // execute the operation
            $result[$operation['name']] = $this->executeOperation($operation, $obj, $nextState);
            if ($result[$operation['name']] === false) {
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
            $this->workflowData = $obj['__WORKFLOW__'];
        }

        // change the workflow state
        if (!$this->updateWorkflowState($nextState)) {
            return false;
        }

        // updates the workflow state value
        if ($obj instanceof Doctrine_Record) {
            $this->workflowData['state'] = $nextState;
            $obj->mapValue('__WORKFLOW__', $this->workflowData);
        } else {
            $obj['__WORKFLOW__']['state'] = $nextState;
        }

        // return result of all operations (possibly okay to just return true here)
        return $result;
    }

    /**
     * Execute workflow operation within action.
     *
     * @param string $operation  Operation name.
     * @param array  &$obj       Data object.
     * @param string &$nextState Next state.
     *
     * @return mixed|false
     */
    public function executeOperation($operation, &$obj, &$nextState)
    {
        $params = $operation['parameters'];
        if (isset($params['nextstate'])) {
            $nextState = $params['nextstate'];
        }
        $params['nextstate'] = $nextState;

        // test operation file exists
        $path = Zikula_Workflow_Util::_findpath("operations/function.{$operation['name']}.php", $this->module);
        if (!$path) {
            return z_exit(__f('Operation file [%s] does not exist', $operation['name']));
        }

        // load file and test if function exists
        include_once $path;
        $function = "{$this->module}_operation_{$operation['name']}";
        if (!function_exists($function)) {
            return z_exit(__f('Operation function [%s] is not defined', $function));
        }

        // execute operation and return result
        $result = $function($obj, $params);

        $states = array_keys($this->stateMap);
        // checks for an invalid next state value
        if (!in_array($params['nextstate'], $states)) {
            LogUtil::registerError(__f('Invalid next-state value [%1$s] retrieved by the \'%2$s\' operation for the workflow \'%3$s\' [\'%4$s\'].', array($nextState, $operation, $this->getID(), $this->getModule())));
        } else {
            $nextState = $params['nextstate'];
        }

        return $result;
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
