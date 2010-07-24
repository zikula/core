<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPL3 (or at your option any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Zikula_Workflow_Parser.
 *
 * Parse workflow schema into associative arrays.
 */
class Zikula_Workflow_Parser
{
    // Declare object variables;
    /**
     * XML parser object.
     *
     * @var object
     */
    protected $parser;

    /**
     * Workflow data.
     *
     * @var array
     */
    protected $workflow;

    /**
     * Parse workflow into array format.
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
     * @param string $xmldata    XML data.
     * @param string $schemaName Schema name.
     * @param string $module     Module name.
     *
     * @return mixed Associative array of workflow or false.
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
     * Map workflow.
     *
     * Marshall data in to meaningful associative arrays.
     *
     * @return void
     */
    public function mapWorkflow()
    {
        // create associative arrays maps
        $stateMap  = array();
        $actionMap = array();

        foreach ($this->workflow['states'] as $state) {
            $stateMap[$state['id']] = array($state['id'], $state['title'], $state['description']);
        }

        $states = array_keys($stateMap);

        foreach ($this->workflow['actions'] as $action) {
            if (($action['state'] == 'initial') || ($action['state'] == null) || in_array($action['state'], $states)) {
                $action['state'] = $stateID = !empty($action['state']) ? $action['state'] : 'initial';

                // change the case of array keys for parameter variables
                $operations = &$action['operations'];
                foreach (array_keys($operations) as $key) {
                    $parameters = &$operations[$key]['parameters'];
                    $parameters = array_change_key_case($parameters, CASE_LOWER);
                }

                // commit results
                $actionMap[$stateID][$action['id']] = $action;
            } else {
                LogUtil::registerError(__f('Unknown %1$s name \'%2$s\' in action \'%3$s\'.', array('state', $action['state'], $action['title'])));
            }
        }

        // commit new array to workflow
        $this->workflow['states']  = $stateMap;
        $this->workflow['actions'] = $actionMap;
    }

    /**
     * Validate workflow actions.
     *
     * @return boolean
     */
    public function validate()
    {
        foreach (array_keys($this->workflow['actions']) as $stateID) {
            foreach ($this->workflow['actions'][$stateID] as $action) {
                if (isset($action['nextState']) && !isset($this->workflow['states'][$action['nextState']])) {
                    return LogUtil::registerError(__f('Unknown %1$s name \'%2$s\' in action \'%3$s\' of the \'%4$s\' state.', array('next-state', $action['nextState'], $action['title'], $action['state'])));
                }

                foreach ($action['operations'] as $operation) {
                    if (isset($operation['parameters']['nextState'])) {
                        $stateName = $operation['parameters']['nextState'];
                        if (!isset($this->workflow['states'][$stateName])) {
                            return LogUtil::registerError(__f('Unknown state name \'%1$s\' in action \'%2$s\', operation \'%3$s\'.', array($stateName, $action['title'], $operation['name'])));
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * XML start element handler.
     *
     * @param object $parser  Parser object.
     * @param string $name    Element name.
     * @param array  $attribs Element attributes.
     *
     * @return void
     */
    public function startElement($parser, $name, $attribs)
    {
        $name = strtoupper($name);
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
            xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
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
            } else if ($name == 'PARAMETER') {
                $this->workflow['value'] = '';
                $this->workflow['actionParameter'] = $attribs;
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
     * XML end element handler.
     *
     * @param object $parser Parser object.
     * @param string $name   Element name.
     *
     * @return void
     */
    public function endElement($parser, $name)
    {
        $name = strtoupper($name);
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
            } else if ($name == 'PARAMETER') {
                $this->workflow['action']['parameters'][trim($this->workflow['value'])] = $this->workflow['actionParameter'];
            } else if ($name == 'NEXTSTATE') {
                $this->workflow['action']['nextState'] = trim($this->workflow['value']);
            } else if ($name == 'ACTION') {
                xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 1);
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
     * XML data element handler.
     *
     * @param object $parser Parser object.
     * @param string $data   Character data.
     *
     * @return void
     */
    public function characterData($parser, $data)
    {
        $value  = &$this->workflow['value'];
        $value .= $data;
        return true;
    }

    /**
     * Hander for unexpected XML errors.
     *
     * @param string $name  Tag name.
     * @param string $state Workflow state.
     *
     * @return string
     */
    public function unexpectedXMLError($name, $state)
    {
        return __f('Unexpected %1$s tag in %2$s state', array($name, $state));
    }
}
