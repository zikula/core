<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_DebugToolbar
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * This panel displays all module & moduleapi executions.
 */
class Zikula_DebugToolbar_Panel_Exec implements Zikula_DebugToolbar_PanelInterface
{
    const RECURSIVE_LIMIT = 10;

    /**
     * These objects won't be displayed by this panel because they are to big.
     *
     * @var array
     */
    private static $OBJECTS_TO_SKIPP = array('Zikula_ServiceManager', 'Zikula_View',
                                             'Zikula_EventManager', 'Doctrine_Table');

    /**
     * Contains all executed module functions.
     *
     * @var array
     */
    private $_executions = array();

    /**
     * Stack of nested module func executions.
     *
     * @var array
     */
    private $_stack = array();

    /**
     * Returns the id of this panel.
     *
     * @return string
     */
    public function getId()
    {
        return "exec";
    }

    /**
     * Returns the link name.
     *
     * @return string
     */
    public function getTitle()
    {
        return __('Functions executions');
    }

    /**
     * Returns the content panel title.
     *
     * @return string
     */
    public function getPanelTitle()
    {
        return __('Module Func/API executions');
    }

    /**
     * Returns the the HTML code of the content panel.
     *
     * @return string HTML
     */
    public function getPanelContent()
    {
        $rows = array();
        $index = 1;
        foreach ($this->_executions as $exec) {
            $id = 'DebugToolbarExecPanel' . $index;
            $index++;

            $argsShort = $this->buildArgumentsPreview($exec['args']);

            if (strlen($argsShort) >= 100) {
                $idArgs = $id . 'args';
                $args = '<a href="" title="'.__('Click to show the full parameter list').'" onclick="$(\''.$idArgs.'\').toggle();return false;">' . $argsShort . '</a><div style="display:none" id="'.$idArgs.'">' . $this->formatVar('', $exec['args']) . '</div>';
            } else {
                $args = $argsShort;
            }

            if (!is_string($exec['data'])) {
                $exec['data'] = $this->formatVar('', $exec['data']);
            } else {
                $exec['data'] = '<pre>' . DataUtil::formatForDisplay($exec['data']) . '</pre>';
            }

            $rows[] = '<tr>
                           <td><a href="#" title="'.__('Click to show return value').'" onclick="$(\''.$id.'\').toggle();return false;">'.$this->_levelToSpaces($exec['level']).' '.$exec['module'].'/'.$exec['type'].$exec['api'].'/'.$exec['func'].'</a>('.$args.')</td>
                           <td>'.round($exec['time'], 3).'</td>
                       </tr>
                       <tr id="'.$id.'" style="display: none;">
                           <td>' . $exec['data'] . '</td>
                       </tr>';
        }

        return '<table>
                    <tr>
                        <th>'.__('Function').'</th>
                        <th>'.__('Time (ms)').'</th>
                    </tr>
                    '.implode(' ', $rows).'
                </table>';
    }

    /**
     * Repeats the string ' --' $level times.
     *
     * @param integer $level Level.
     *
     * @return string
     */
    private function _levelToSpaces($level)
    {
        $html = '';

        for ($i=0; $i < $level; $i++) {
            $html .= ' --';
        }

        return $html;
    }

    /**
     * Builds an preview of an value.
     *
     * The preview won't contain any array/object contents.
     *
     * @param mixed $args Value to build an preview from.
     *
     * @return string
     */
    protected function buildArgumentsPreview($args)
    {
        $preview = '';
        $inArray = false;

        if (is_array($args)) {
            $preview = 'array(';
            $inArray = true;
        }

        $args = (array)$args;
        $isFirstIteration = true;
        foreach ($args as $key => $value) {
            $valuePrefix = ($inArray && is_string($key)? $key . ' => ' : '' );

            if (!$isFirstIteration) {
                $preview .= ', ';
            }
            $isFirstIteration = false;

            if (is_numeric($value) || is_bool($value)) {
                $preview .= $valuePrefix . $value;
            } elseif (is_string($value)) {
                $preview .= $valuePrefix . '"' . DataUtil::formatForDisplay($value) . '"';
            } elseif (is_array($value)) {
                $preview .= $valuePrefix . 'array(...)';
            } elseif (is_object($value)) {
                $preview .= $valuePrefix . get_class($value) . '{...}';
            } elseif (is_null($value)) {
                $preview .= $valuePrefix . 'NULL';
            } elseif (is_nan($value)) {
                $preview .= $valuePrefix . 'NAN';
            } else {
                $preview .= $valuePrefix . '?';
            }
        }

        if ($inArray) {
            $preview .= ')';
        }

        return $preview;
    }

    /**
     * Creates an ul/li list a value (recursive safe).
     *
     * @param mixed   $key   Name of the variable.
     * @param mixed   $var   Value of the variable.
     * @param integer $level Deep of the formatting.
     *
     * @return string HTML-Code
     */
    protected function formatVar($key, $var, $level=1)
    {
        $html = '';
        if ($level > self::RECURSIVE_LIMIT) {
            return '...';
        } elseif (is_object($var)) {
            $html =  "<strong>" . $key . '</strong>  <span style="color:#666666;font-style:italic;">('.
                       get_class($var).')</span>: <ul>';

            if (get_class($var) == 'Doctrine_Record' || get_class($var) == 'Doctrine_Collection') {
                $var = $var->toArray();
                foreach ($var as $akey => $avar) {
                    $html .= $this->formatVar($akey, $avar, $level + 1);
                }

            } elseif (!in_array(get_class($var), self::$OBJECTS_TO_SKIPP)) {
                $cls = new ReflectionClass($var);
                foreach ($cls->getProperties() as $prop) {
                    $prop->setAccessible(true);
                    $html .= $this->formatVar($prop->name, $prop->getValue($var), $level + 1);
                }
            }

            $html .= '</ul>';
        } elseif (is_array($var)) {
            $html =  '<code>' . $key . '</code> <span style="color:#666666;font-style:italic;">(array)</span>: <ul>';

            if (!empty($var) && (count($var) > 0)) {
                foreach ($var as $akey => $avar) {
                    $html .= $this->formatVar($akey, $avar, $level + 1);
                }
            } else {
                $html .= '<li><em>'.__('(empty)').'</em></li>';
            }

            $html .= '</ul>';

        } else {
            $html =  '<code>' . $key . '</code> <span style="color:#666666;font-style:italic;">('.
                        gettype($var).')</span>: <pre class="DebugToolbarVarDump">' . DataUtil::formatForDisplay($var) . '</pre>';
        }


        $html = '<li>' . $html . '</li>';

        if ($level == 1) {
            $html = '<ul>' . $html . '</ul>';
        }

        return $html;
    }

    /**
     * Event listener for module.preexecute.
     *
     * @param Zikula_Event $event Event.
     *
     * @return void
     */
    public function modexecPre(Zikula_Event $event)
    {
        $modfunc = $event['modfunc'];
        if (is_array($modfunc)) {
            $modfunc = $modfunc[1];
        }

        $this->_executions[] = array('module' => $event['modinfo']['name'],
                                     'type'   => $event['type'],
                                     'api'    => $event['api']? 'api' : '',
                                     'func'   => str_replace($event['modinfo']['name'].'_'.$event['type'].($event['api']? 'api' : '').'_', '', $modfunc),
                                     'args'   => $event['args'],
                                     'time'   => microtime(true),
                                     // default values
                                     'level'  => 0,
                                     'data'   => null);

        $this->_stack[] = count($this->_executions) - 1;
    }

    /**
     * Event listener for module.postexecute.
     *
     * @param Zikula_Event $event Event.
     *
     * @return void
     */
    public function modexecPost(Zikula_Event $event)
    {
        if (count($this->_stack) == 0) {
            return;
        }

        // pos to last stack entry
        $stackPos = count($this->_stack) -1;

        // extract value of stack entry
        $lastExecPos = $this->_stack[$stackPos];

        // remove from stack
        unset($this->_stack[$stackPos]);
        $this->_stack = array_values($this->_stack);

        // calculate time
        $startTime = $this->_executions[$lastExecPos]['time'];
        $this->_executions[$lastExecPos]['time'] = (microtime(true) - $startTime) * 1000;
        $this->_executions[$lastExecPos]['level'] = count($this->_stack);
        $this->_executions[$lastExecPos]['data'] = $event->getData();
    }

    /**
     * Returns the panel data in raw format.
     *
     * @return array
     */
    public function getPanelData()
    {
        return $this->_executions;
    }
}
