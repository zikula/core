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
 * This panel displays an log console.
 */
class Zikula_DebugToolbar_Panel_Log implements Zikula_DebugToolbar_PanelInterface
{
    /**
     * Contains the log entries to display.
     *
     * @var array
     */
    private $_log = array();

    /**
     * Returns the id of this panel.
     *
     * @return string
     */
    public function getId()
    {
        return "logs";
    }

    /**
     * Returns the link name.
     *
     * @return string
     */
    public function getTitle()
    {
        $title =  __('Log console');
        $count = count($this->_log);

        if ($count > 0) {
            $title .= " ({$count})";
        }

        return $title;
    }

    /**
     * Returns the content panel title.
     *
     * @return string
     */
    public function getPanelTitle()
    {
        return __('Log console');
    }

    /**
     * Returns the the HTML code of the content panel.
     *
     * @return string HTML
     */
    public function getPanelContent()
    {
        $rows = array();

        foreach ($this->_log as $log) {
            $hasFileAndLine = isset($log['errfile']) && $log['errfile'] && isset($log['errline']) &&$log['errline'];
            $rows[] = '<tr class="DebugToolbarType'.$log['type'].'">
                           <td class="DebugToolbarLogsType">'.$this->getImageForErrorType($log['type']).' '.$this->errorTypeToString($log['type']).'</td>
                           <td class="DebugToolbarLogsMessage">'.DataUtil::formatForDisplay($log['errstr']).'</td>
                           <td class="DebugToolbarLogsFile">'.($hasFileAndLine? $log['errfile'].':'.$log['errline'] : '-').'</td>
                       </tr>';
        }

        if (empty($rows)) {
            $rows[] = '<tr>
                           <td colspan="3">'.__('No items found.').'</td>
                       </tr>';

        }

        return '<table class="DebugToolbarTable">
                    <tr>
                        <th class="DebugToolbarLogsType">'.__('Type').'</th>
                        <th class="DebugToolbarLogsMessage">'.__('Message').'</th>
                        <th class="DebugToolbarLogsFile">'.__('File: Line').'</th>
                    </tr>
                    '.implode(' ', $rows).'
                </table>';
    }

    /**
     * Converts an error type to an string.
     *
     * @param int $type Error type form Zikula_AbstractErrorHandler.
     *
     * @return string String representation
     */
    protected function errorTypeToString($type)
    {
        switch ($type) {
            case Zikula_AbstractErrorHandler::EMERG:
                return __('Emergency');
            case Zikula_AbstractErrorHandler::ALERT:
                return __('Alert');
            case Zikula_AbstractErrorHandler::CRIT:
                return __('Critical');
            case Zikula_AbstractErrorHandler::ERR:
                return __('Error');
            case Zikula_AbstractErrorHandler::WARN:
                return __('Warning');
            case Zikula_AbstractErrorHandler::NOTICE:
                return __('Notice');
            case Zikula_AbstractErrorHandler::INFO:
                return __('Informational');
            case Zikula_AbstractErrorHandler::DEBUG:
                return __('Debug');
            default:
                return __('Unknown');
        }
    }

    /**
     * Returns HTML-Code for an image representing the error type.
     *
     * @param int $type Error type form Zikula_AbstractErrorHandler.
     *
     * @return string HTML
     */
    protected function getImageForErrorType($type)
    {
        switch ($type) {
            case Zikula_AbstractErrorHandler::EMERG:
                return '<img src="'.System::getBaseUri().'/images/icons/extrasmall/exit.png" alt="" />';
            case Zikula_AbstractErrorHandler::ALERT:
                return '<img src="'.System::getBaseUri().'/images/icons/extrasmall/error.png" alt="" />';
            case Zikula_AbstractErrorHandler::CRIT:
                return '<img src="'.System::getBaseUri().'/images/icons/extrasmall/error.png" alt="" />';
            case Zikula_AbstractErrorHandler::ERR:
                return '<img src="'.System::getBaseUri().'/images/icons/extrasmall/error.png" alt="" />';
            case Zikula_AbstractErrorHandler::WARN:
                return '<img src="'.System::getBaseUri().'/images/icons/extrasmall/redled.png" alt="" />';
            case Zikula_AbstractErrorHandler::NOTICE:
                return '<img src="'.System::getBaseUri().'/images/icons/extrasmall/info.png" alt="" />';
            case Zikula_AbstractErrorHandler::INFO:
                return '<img src="'.System::getBaseUri().'/images/icons/extrasmall/info.png" alt="" />';
            case Zikula_AbstractErrorHandler::DEBUG:
                return '<img src="'.System::getBaseUri().'/images/icons/extrasmall/text_block.png" alt="" />';
            default:
                return __('Unknown');
        }
    }

    /**
     * Event listener for module.execute_not_found.
     *
     * @param Zikula_Event $event Event.
     *
     * @return void
     */
    public function logExecNotFound(Zikula_Event $event)
    {
        $this->_log[] = array('type'    =>  Zikula_AbstractErrorHandler::EMERG,
                              'errstr' => 'Execute Function failed: Function not found '.$event['modfunc']);
    }

    /**
     * Event listener for log.
     *
     * @param Zikula_Event $event Event.
     *
     * @return void
     */
    public function log(Zikula_Event $event)
    {
        $this->_log[] = $event->getArgs();
    }

    /**
     * Event listener for controller.method_not_found.
     *
     * @param Zikula_Event $event Event.
     *
     * @return void
     */
    public function logModControllerNotFound(Zikula_Event $event)
    {
        $this->_log[] = array('type'    => Zikula_AbstractErrorHandler::EMERG,
                              'errstr' => 'Execute Controller method failed: Method not found '.get_class($event->getSubject()).'->'.$event['method']);
    }

    /**
     * Event listener for controller_api.method_not_found.
     *
     * @param Zikula_Event $event Event.
     *
     * @return void
     */
    public function logModControllerAPINotFound(Zikula_Event $event)
    {
        $this->_log[] = array('type'   =>  Zikula_AbstractErrorHandler::EMERG,
                              'errstr' => 'Execute Controller API method failed: Method not found '.get_class($event->getSubject()).'->'.$event['method']);
    }

    /**
     * Returns the panel data in raw format.
     *
     * @return array
     */
    public function getPanelData()
    {
        $data = array();
        foreach ($this->_log as $k => $v) {
            if (isset($v['trace'])) {
                foreach ($v['trace'] as $kt => $vt) {
                    if (isset($vt['object'])) {
                        // need to truncate object entry in trace items because it's generating enormous amount of data
                        $v['trace'][$kt]['object'] = Zikula_DebugToolbar::prepareData($vt['object'], -1);
                    }
                    $v['trace'][$kt]['args'] = Zikula_DebugToolbar::prepareData($vt['args']);
                }
            }
            $data[$k] = $v;
        }

        return $data;
    }
}
