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
class Zikula_DebugToolbar_Panel_Exec implements Zikula_DebugToolbar_Panel
{
    /**
     * Contains all executed module functions.
     *
     * @var array
     */
    private $_executions = array();

    /**
     * Stack of nexted module func executions.
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

            $args = print_r($exec['args'], true);

            if (strlen($args) > 100) {
                $shortargs = substr($args, 0, 100);
                $shortargs .= '...';
                $idArgs = $id . 'args';
                $args = '<a href="" title="'.__('Click to show the full parameter list').'" onclick="$(\''.$idArgs.'\').toggle();return false;">' . $shortargs . '</a><span style="display:none" id="'.$idArgs.'">'.substr($args, 100).'</span>';
            }

            if (!is_string($exec['data'])) {
                ob_start();
                var_dump($exec['data']);
                $exec['data'] = ob_get_contents();
                ob_end_clean();
            }

            $rows[] = '<tr>
                           <td><a href="#" title="'.__('Click to show return value').'" onclick="$(\''.$id.'\').toggle();return false;">'.$this->_levelToSpaces($exec['level']).' '.$exec['module'].'/'.$exec['type'].$exec['api'].'/'.$exec['func'].'</a>('.$args.')</td>
                           <td>'.round($exec['time'], 3).'</td>
                       </tr>
                       <tr id="'.$id.'" style="display: none;">
                           <td><pre>'.DataUtil::formatForDisplay($exec['data']).'</pre></td>
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
}
