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
 * This panel displays the configuration of the current top level module and the zikula configuration.
 */
class Zikula_DebugToolbar_Panel_Config implements Zikula_DebugToolbar_PanelInterface
{
    /**
     * Returns the id of this panel.
     *
     * @return string
     */
    public function getId()
    {
        return "config";
    }

    /**
     * Returns the link name.
     *
     * @return string
     */
    public function getTitle()
    {
        return __('Configuration');
    }

    /**
     * Returns the content panel title.
     *
     * @return string
     */
    public function getPanelTitle()
    {
        return __('Configuration');
    }

    /**
     * Returns the the HTML code of the content panel.
     *
     * @return string HTML
     */
    public function getPanelContent()
    {
        $html = '';

        // zikula config
        $html .= $this->arrayToHTML(__('Zikula configuration'), 'ZConfig', $GLOBALS['ZConfig']);

        // current top level module
        $module = ModUtil::getName();
        if ($module) {
            $html .= $this->arrayToHTML(__f('Module %s', $module), 'Module vars', ModUtil::getVar($module));
        }

        return $html;
    }

    /**
     * Converts an array to an nexted list.
     *
     * @param string $name      Main name.
     * @param string $arrayname Sub name.
     * @param array  $array     Data.
     *
     * @return string
     */
    protected function arrayToHTML($name, $arrayname, $array)
    {
        $id = str_replace(' ', '', $name);

        $html = '<h2><a href="#" title="'.__('Click to show the configuration variables').'" onclick="$(\'DebugToolbarPanelconfigContent'.$id.'\').toggle();return false;">'.$name.'</a></h2>';
        $html .= '<div id="DebugToolbarPanelconfigContent'.$id.'" style="display:none;">';
        $html .= '<ul>' . $this->outputVar($arrayname, $array) .'</ul>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Converts an array to an nexted list.
     *
     * @param string $key Key to display to the value.
     * @param mixed  $var Value to diaplay.
     *
     * @return string
     */
    protected function outputVar($key, $var)
    {
        if (!is_array($var)) {
            return "<li><strong>{$key}:</strong> <pre class=\"DebugToolbarVarDump\">" . DataUtil::formatForDisplay($var) . '</pre></li>';
        } else {
            $html =  "<li><strong>" . $key . ':</strong> <ul>';

            if (!empty($var) && (count($var) > 0)) {
                foreach ($var as $akey => $avar) {
                    $html .= $this->outputVar($akey, $avar);
                }
            } else {
                $html .= '<li><em>'.__('(empty)').'</em></li>';
            }

            $html .= "</ul></li>";

            return $html;
        }
    }

    /**
     * Returns the panel data in raw format.
     *
     * @return array
     */
    public function getPanelData()
    {
        $data = array();

        // zikula config
        $data['global'] = array(
            'title' => __('Zikula configuration'),
            'content' => array('ZConfig' => $GLOBALS['ZConfig'])
        );

        // current top level module
        $module = ModUtil::getName();
        if ($module) {
            $data[$module] = array(
                'title' => __f('Module %s', $module),
                'content' => ModUtil::getVar($module)
            );
        }

        return $data;
    }
}
