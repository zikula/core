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
 * Zikula_Workflow class.
 *
 * @deprecated
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

        $insertObj = [
            'obj_table'    => $obj['__WORKFLOW__']['obj_table'],
            'obj_idcolumn' => $obj['__WORKFLOW__']['obj_idcolumn'],
            'obj_id'       => $obj[$idcolumn],
            'module'       => $this->getModule(),
            'schemaname'   => $this->id,
            'state'        => $stateID
        ];

        $entity = new Zikula\Core\Doctrine\Entity\WorkflowEntity();
        $entity->setObjTable($insertObj['obj_table']);
        $entity->setObjIdcolumn($insertObj['obj_idcolumn']);
        $entity->setObjId($insertObj['obj_id']);
        $entity->setModule($insertObj['module']);
        $entity->setSchemaname($insertObj['schemaname']);
        $entity->setState($insertObj['state']);

        //get the entity manager
        $em = ServiceUtil::get('doctrine.entitymanager');
        $em->persist($entity);
        $em->flush();

        $insertObj['id'] = $entity->getId();

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
        //get the entity manager
        $em = ServiceUtil::get('doctrine.entitymanager');

        //create the dql query.
        $qb = $em->createQueryBuilder()
                 ->update('Zikula\Core\Doctrine\Entity\WorkflowEntity', 'w')
                 ->set('w.state', ':newState')
                 ->setParameter('newState', $stateID);

        if (isset($debug)) {
            $qb->set('w.debug', ':newDebug')
               ->setParameter('newDebug', $debug);
        }

        $query = $qb->where('w.id = :id')
                    ->setParameter('id', $this->workflowData['id'])
                    ->getQuery();

        $result = $query->execute();

        return true;
    }

    /**
     * Execute workflow action.
     *
     * @param string $actionID Action Id.
     * @param array  &$obj     Data object.
     * @param string $stateID State Id.
     *
     * @return mixed Array or false.
     */
    public function executeAction($actionID, &$obj, $stateID = 'initial')
    {
        // check if state exists
        if (!isset($this->actionMap[$stateID])) {
            throw new \Exception("STATE: $stateID not found");
        }

        // check the action exists for given state
        if (!isset($this->actionMap[$stateID][$actionID])) {
            throw new \Exception(__f('Action: %1$s not available in this State: %2$s', [$actionID, $stateID]));
        }

        $action = $this->actionMap[$stateID][$actionID];

        // permission check
        if (!Zikula_Workflow_Util::permissionCheck($this->module, $this->id, $obj, $action['permission'], $action['id'])) {
            throw new \Exception(__f('No permission to execute action: %s [permission]', $action['id']));
        }

        // commit workflow to object
        $this->workflowData = $obj['__WORKFLOW__'];

        // define the next state to be passed to the operations
        $nextState = (isset($action['nextState']) ? $action['nextState'] : $stateID);

        // process the action operations
        $result = [];
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
            // use helper var to prevent indirect modification exception
            $workflow = $obj['__WORKFLOW__'];
            $workflow['state'] = $nextState;
            $obj['__WORKFLOW__'] = $workflow;
        }

        // return result of all operations (possibly okay to just return true here)
        return $result;
    }

    /**
     * Execute workflow operation within action.
     *
     * @param string $operation Operation name.
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
            throw new \Exception(__f('Operation file [%s] does not exist', $operation['name']));
        }

        // load file and test if function exists
        include_once $path;
        $function = "{$this->module}_operation_{$operation['name']}";
        if (!function_exists($function)) {
            throw new \Exception(__f('Operation function [%s] is not defined', $function));
        }

        // execute operation and return result
        $result = $function($obj, $params);

        $states = array_keys($this->stateMap);
        // checks for an invalid next state value
        if (!in_array($params['nextstate'], $states)) {
            LogUtil::addErrorPopup(__f('Invalid next-state value [%1$s] retrieved by the \'%2$s\' operation for the workflow \'%3$s\' [\'%4$s\'].', [$nextState, $operation, $this->getID(), $this->getModule()]));
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
