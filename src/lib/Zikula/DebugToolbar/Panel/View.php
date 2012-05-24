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
 * This panel displays the assigned variables of all renderd templates.
 */
class Zikula_DebugToolbar_Panel_View implements Zikula_DebugToolbar_PanelInterface
{
    /**
     * Contains all rendert templates with its assigned template variables.
     *
     * @var array
     */
    private $_templates = array();

    /**
     * Returns the id of this panel.
     *
     * @return string
     */
    public function getId()
    {
        return "view";
    }

    /**
     * Returns the link name.
     *
     * @return string
     */
    public function getTitle()
    {
        return __('Templates');
    }

    /**
     * Returns the content panel title.
     *
     * @return string
     */
    public function getPanelTitle()
    {
        return __('Templates');
    }

    /**
     * Returns the the HTML code of the content panel.
     *
     * @return string HTML
     */
    public function getPanelContent()
    {
        $rows = array();

        foreach ($this->_templates as $template) {
            $rows[] = $this->arrayToHTML($template['template'], $template['vars']);
        }

        return implode(' ', $rows);
    }

    /**
     * Converts all assigned template variables to HTML code.
     *
     * @param string $name  Name of the Template.
     * @param array  $array Array of all assigned template variables.
     *
     * @return string
     */
    protected function arrayToHTML($name, $array)
    {
        $id = substr($name, 0, strpos($name, '.'));

        $html = '<h2><a href="#" title="'.__('Click to show the assigned template variables').'" onclick="$(\'DebugToolbarPanelTemplateVarContent'.$id.'\').toggle();return false;">'.$name.'</a></h2>';
        $html .= '<div id="DebugToolbarPanelTemplateVarContent'.$id.'" style="display:none;">';
        $html .= $this->outputVar('', $array, true);
        $html .= '</div>';

        return $html;
    }

    /**
     * Creates an ul/li list of an array (recursive safe).
     *
     * @param mixed $key          Name of the variable.
     * @param mixed $var          Value of the variable.
     * @param bool  $isFirstLevel True does not display the variable name (default: false).
     *
     * @return string
     */
    protected function outputVar($key, $var, $isFirstLevel=false)
    {
        if (is_object($var)) {
            $html =  "<li><strong>" . $key . '</strong>  <span style="color:#666666;font-style:italic;">('.
                       get_class($var).')</span>: <ul>';

            $cls = new ReflectionClass(get_class($var));
            foreach ($cls->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
                $html .= $this->outputVar($prop->getName(), $prop->getValue($var));
            }

            foreach ($cls->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                $html .= '<li><strong>' . $key . '->'.$method->getName().':</strong><ul>';
            }

            $html .= '</ul></li>';
        } elseif (is_array($var)) {
            if (!$isFirstLevel) {
                $html =  '<li><code>{$' . $key . '}</code> <span style="color:#666666;font-style:italic;">(array)</span>: <ul>';
            } else {
                $html =  '<ul>';
            }

            if (!empty($var) && (count($var) > 0)) {
                foreach ($var as $akey => $avar) {
                    $akey = (strpos($akey, '[') !== false)? '["'.$akey.'"]' : ($isFirstLevel? $akey : '.'.$akey);
                    $html .= $this->outputVar(($isFirstLevel? $akey : $key.$akey), $avar);
                }
            } else {
                $html .= '<li><em>'.__('(empty)').'</em></li>';
            }

            if (!$isFirstLevel) {
                $html .= '</ul></li>';
            } else {
                $html .= '</ul>';
            }

            return $html;
        } else {
            return '<li><code>{$' . $key . '}</code> <span style="color:#666666;font-style:italic;">('.
                     gettype($var).')</span>: <pre class="DebugToolbarVarDump">' . DataUtil::formatForDisplay($var) . '</pre></li>';
        }
    }

    /**
     * Listener which modifies the Theme Renderer.
     *
     * @param Zikula_Event $event Event.
     *
     * @return void
     */
    public function initRenderer(Zikula_Event $event)
    {
        $view = $event->getSubject();
        $view->debugging = true;
        $view->register_outputfilter(array($this, 'smartyViewoutputfilter'));
    }

    /**
     * This smarty output filter saves all assigned templates variables.
     *
     * @param string      $output Current HTML output.
     * @param Zikula_View $view   Current Zikula_View instance.
     *
     * @return string
     */
    public function smartyViewoutputfilter($output, $view)
    {
        // extract template name
        if (isset($view->_smarty_debug_info[0])) {
            $template = $view->_smarty_debug_info[0]['filename'];
            $templatepath = $view->get_template_path($template);

            // extract module
            $templateModule = substr($templatepath, strpos($templatepath, '/')+1);
            $templateModule = substr($templateModule, 0, strpos($templateModule, '/'));

            $this->_templates[] = array('module'    => $templateModule,
                                        'template'  => $template,
                                        'vars'      => $this->removeOldModuleVars($this->removeZikulaViewVars($view->get_template_vars())));

            $view->_smarty_debug_info = array();
        }

        return $output;
    }


    /**
     * Remoes all problematic template vars assigned by Zikula_View.
     *
     * @param array $vars Variables.
     *
     * @return array
     */
    protected function removeZikulaViewVars($vars)
    {
        unset($vars['zikula_view']); // results in endless loop

        $themeVars = array_keys(ThemeUtil::getVar());
        foreach ($themeVars as $var) {
            unset($vars[$var]);
        }

        return $vars;
    }

    /**
     * Removes all variables from $vars that has been available since a older assign.
     *
     * @param array $vars Variables.
     *
     * @return array
     */
    protected function removeOldModuleVars($vars)
    {
        foreach ($this->_templates as $template) {
            foreach ($template['vars'] as $var => $value) {
                if (isset($vars[$var]) && $vars[$var] === $value) {
                    unset($vars[$var]);
                }
            }
        }

        return $vars;
    }

    /**
     * Returns the panel data in raw format.
     *
     * @return array
     */
    public function getPanelData()
    {
        $data = array();
        foreach ($this->_templates as $k => $v) {
            foreach ($v['vars'] as $kv => $vv) {
                $v['vars'][$kv] = Zikula_DebugToolbar::prepareData($v['vars'][$kv]);
            }
            $data[$k] = $v;
        }

        return $data;
    }
}
