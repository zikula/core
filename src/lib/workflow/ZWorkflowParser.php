<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option any later version).
 * @package ZWorkflow
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Workflow Parser
 * Parse workflow schema into associative arrays
 */
class ZWorkflowParser
{
    // Declare object variables;
    protected $parser;
    protected $workflow;

    /**
     * parse workflow into array format
     *
     */
    public function __construct()
    {
        $this->workflow = array('state' => 'initial');

        // create xml parser
        $this->parser = xml_parser_create();
        xml_set_object($this->parser, $this);
        xml_set_element_handler($this->parser, 'startElement', 'endElement');
        xml_set_character_data_handler($this->parser, 'characterData');
    }

    /**
     * parse xml
     *
     * @param string $xmldata
     * @param string $schemaName
     * @param string $module
     * @param string $schemaPath
     * @return mixed associative array of workflow or false
     */
    public function parse($xmldata, $schemaName, $module)
    {
        // parse XML
        if (!xml_parse($this->parser, $xmldata, true)) {
            xml_parser_free($this->parser);
            z_exit(__f('Unable to parse XML workflow (line %1$s, %2$s): %3$s',
                        array(xml_get_current_line_number($this->parser),
                              xml_get_current_column_number($this->parser),
                              xml_error_string($this->parser))));
        }

        // close parser
        xml_parser_free($this->parser);

        // check for errors
        if ($this->workflow['state'] == 'error') {
            return LogUtil::registerError($this->workflow['errorMessage']);
        }

        $this->mapWorkflow();

        if (!$this->validate()) {
            return false;
        }

        $this->workflow['workflow']['module'] = $module;
        $this->workflow['workflow']['id'] = $schemaName;

        return $this->workflow;
    }

    /**
     * Map workflow
     * marshall data in to meaningful associative arrays
     *
     */
    public function mapWorkflow()
    {
        $states = $this->workflow['states'];
        $actions = $this->workflow['actions'];

        // create associative arrays maps
        $actionMap = array();
        $stateMap = array();

        foreach ($states as $state) {
            $stateMap[$state['id']] = array($state['id'], $state['title'], $state['description']);
            foreach ($actions as $action) {
                if (($action['state'] == 'initial') || ($action['state'] == null) || ($action['state'] == $state['id'])) {
                    if ($action['state'] == 'initial' || $action['state'] == null) {
                        $stateID = 'initial';
                    } else if (($action['state']) == $state['id']) {
                        $stateID = $state['id'];
                    }

                    // change the case of array keys for parameter variables
                    $operations = &$action['operations'];
                    $ak = array_keys($operations);
                    foreach ($ak as $key) {
                        $parameters = &$operations[$key]['parameters'];
                        $parameters = array_change_key_case($parameters, CASE_LOWER);
                    }

                    // commit results
                    $actionID = $action['id'];
                    $actionMap[$stateID][$actionID] = $action;
                }
            }
        }

        // commit new array to workflow
        $this->workflow['actions'] = $actionMap;
        $this->workflow['states'] = $stateMap;
    }

    /**
     * validate workflow actions
     *
     */
    public function validate()
    {
        $stateMap = $this->workflow['states'];
        $states = $this->workflow['actions'];
        $ak = array_keys($states);
        foreach ($ak as $stateID) {
            $actions = $this->workflow['actions'][$stateID];
            foreach ($actions as $action) {
                $stateName = $action['state'];
                if ($stateName != null) {
                    if (!isset($stateMap[$stateName]))
                        return LogUtil::registerError(__f('Unknown %1$s name \'%2$s\' in action \'%3$s\'', array('state', $stateName, $action['title'])));
                }

                if (isset($action['nextState'])) {
                    $nextStateName = $action['nextState'];
                }

                if (isset($nextStateName)) {
                    if (!isset($stateMap[$nextStateName]))
                        return LogUtil::registerError(__f('Unknown %1$s name \'%2$s\' in action \'%3$s\'', array('next-state', $nextStateName, $action['title'])));
                }

                foreach ($action['operations'] as $operation) {
                    if (isset($operation['parameters']['NEXTSTATE'])) {
                        $stateName = $operation['parameters']['NEXTSTATE'];
                        if (!isset($stateMap[$stateName]))
                            return LogUtil::registerError(__f('Unknown state name \'%1$s\' in action \'%2$s\' - operation \'%3$s\'', array($stateName, $action['title'], $operation['name'])));
                    }
                }
            }
        }

        return true;
    }

    /**
     * XML start element handler
     *
     * @access private
     * @param  object $parser
     * @param  string $name
     * @param  array $attribs
     */
    public function startElement($parser, $name, $attribs)
    {
        $state = &$this->workflow['state'];

        if ($state == 'initial') {
            if ($name == 'WORKFLOW') {
                $state = 'workflow';
                $this->workflow['workflow'] = array();
            } else {
                $state = 'error';
                $this->workflow['errorMessage'] = $this->unexpectedXMLError($name, $state . " " . __LINE__);
            }
        } else if ($state == 'workflow') {
            if ($name == 'TITLE' || $name == 'DESCRIPTION') {
                $this->workflow['value'] = '';
            } else if ($name == 'STATES') {
                $state = 'states';
                $this->workflow['states'] = array();
            } else if ($name == 'ACTIONS') {
                $state = 'actions';
                $this->workflow['actions'] = array();
            } else {
                $this->workflow['errorMessage'] = $this->unexpectedXMLError($name, $state . " " . __LINE__);
                $state = 'error';
            }
        } else if ($state == 'states') {
            if ($name == 'STATE') {
                $this->workflow['stateValue'] = array('id' => trim($attribs['ID']));
                $state = 'state';
            } else {
                $this->workflow['errorMessage'] = $this->unexpectedXMLError($name, $state . " " . __LINE__);
                $state = 'error';
            }
        } else if ($state == 'state') {
            if ($name == 'TITLE' || $name == 'DESCRIPTION') {
                $this->workflow['value'] = '';
            } else {
                $this->workflow['errorMessage'] = $this->unexpectedXMLError($name, $state . " " . __LINE__);
                $state = 'error';
            }
        } else if ($state == 'actions') {
            if ($name == 'ACTION') {
                $this->workflow['action'] = array('id' => trim($attribs['ID']), 'operations' => array(), 'state' => null);
                $state = 'action';
            } else {
                $this->workflow['errorMessage'] = $this->unexpectedXMLError($name, $state . " " . __LINE__);
                $state = 'error';
            }
        } else if ($state == 'action') {
            if ($name == 'TITLE' || $name == 'DESCRIPTION' || $name == 'PERMISSION' || $name == 'STATE' || $name == 'NEXTSTATE') {
                $this->workflow['value'] = '';
            } else if ($name == 'OPERATION') {
                $this->workflow['value'] = '';
                $this->workflow['operationParameters'] = $attribs;
            } else {
                $this->workflow['errorMessage'] = $this->unexpectedXMLError($name, $state . " " . __LINE__);
                $state = 'error';
            }
        } else if ($state == '') {
            if ($name == '') {
                $state = '';
            } else {
                $this->workflow['errorMessage'] = $this->unexpectedXMLError($name, $state . " " . __LINE__);
                $state = 'error';
            }
        } else if ($state == 'error') {
            ; // ignore
        } else {
            $this->workflow['errorMessage'] = __('Workflow state error:') . " '$state' " . " '$name'";
            $state = 'error';
        }
    }

    /**
     * XML end element handler
     *
     * @access private
     * @param  object $parser
     * @param  string $name
     */
    public function endElement($parser, $name)
    {
        $state = &$this->workflow['state'];

        if ($state == 'workflow') {
            if ($name == 'TITLE') {
                $this->workflow['workflow']['title'] = $this->workflow['value'];
            } else if ($name == 'DESCRIPTION') {
                $this->workflow['workflow']['description'] = $this->workflow['value'];
            }
        } else if ($state == 'state') {
            if ($name == 'TITLE') {
                $this->workflow['stateValue']['title'] = $this->workflow['value'];
            } else if ($name == 'DESCRIPTION') {
                $this->workflow['stateValue']['description'] = $this->workflow['value'];
            } else if ($name == 'STATE') {
                $this->workflow['states'][] = $this->workflow['stateValue'];
                $this->workflow['stateValue'] = null;
                $state = 'states';
            }
        } else if ($state == 'action') {
            if ($name == 'TITLE') {
                $this->workflow['action']['title'] = $this->workflow['value'];
            } else if ($name == 'DESCRIPTION') {
                $this->workflow['action']['description'] = $this->workflow['value'];
            } else if ($name == 'PERMISSION') {
                $this->workflow['action']['permission'] = trim($this->workflow['value']);
            } else if ($name == 'STATE') {
                $this->workflow['action']['state'] = trim($this->workflow['value']);
            } else if ($name == 'OPERATION') {
                $this->workflow['action']['operations'][] = array('name' => trim($this->workflow['value']), 'parameters' => $this->workflow['operationParameters']);
                $this->workflow['operation'] = null;
            } else if ($name == 'NEXTSTATE') {
                $this->workflow['action']['nextState'] = trim($this->workflow['value']);
            } else if ($name == 'ACTION') {
                $this->workflow['actions'][] = $this->workflow['action'];
                $this->workflow['action'] = null;
                $state = 'actions';
            }
        } else if ($state == 'actions') {
            if ($name == 'ACTIONS') {
                $state = 'workflow';
            }
        } else if ($state == 'states') {
            if ($name == 'STATES') {
                $state = 'workflow';
            }
        }

    }

    /**
     * XML data element handler
     *
     * @access private
     * @param  object $parser parser object
     * @param  string $data
     */
    public function characterData($parser, $data)
    {
        $value = &$this->workflow['value'];
        $value .= $data;
    }

    /**
     * hander for unexpected XML errors
     *
     * @param string $name
     * @param string $state
     * @return string
     */
    public function unexpectedXMLError($name, $state)
    {
        return __f('Unexpected %1$s tag in %2$s state', array($name, $state));
    }
}
