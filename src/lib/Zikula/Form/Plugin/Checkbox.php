<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_Form
 * @subpackage Zikula_Form_AbstractPlugin
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Check box plugin
 *
 * Plugin to generate a checkbox for true/false selection.
 */
class Zikula_Form_Plugin_Checkbox extends Zikula_Form_AbstractStyledPlugin
{
    /**
     * Checked value.
     *
     * Set to true when checkbox is checked, false otherwise.
     *
     * @var boolean
     */
    public $checked;

    /**
     * Enable or disable read only mode.
     *
     * @var boolean
     */
    public $readOnly;

    /**
     * CSS class to use.
     *
     * @var string
     */
    public $cssClass;

    /**
     * Data field name for looking up initial data.
     *
     * The name stored here is used to lookup initial data for the plugin in the render's variables.
     * Defaults to the ID of the plugin. See also tutorials on the Zikula site.
     *
     * @var string
     */
    public $dataField;

    /**
     * Enable or disable use of $dataField.
     *
     * @var boolean
     */
    public $dataBased;

    /**
     * Group name for this input.
     *
     * The group name is used to locate data in the render (when databased) and to restrict which
     * plugins to do validation on (to be implemented).
     *
     * @var string
     * @see   Zikula_Form_View::getValues(), Zikula_Form_View::isValid()
     */
    public $group;

    /**
     * HTML input name for this plugin. Defaults to the ID of the plugin.
     *
     * @var string
     */
    public $inputName;

    /**
     * Get filename for this plugin.
     *
     * A requirement from the framework - must be implemented like this. Used to restore plugins on postback.
     *
     * @internal
     * @return string
     */
    public function getFilename()
    {
        return __FILE__;
    }

    /**
     * Create event handler.
     *
     * @param Zikula_Form_View $view Reference to Form render object.
     * @param array            &$params Parameters passed from the Smarty plugin function.
     *
     * @see    Zikula_Form_AbstractPlugin
     * @return void
     */
    public function create(Zikula_Form_View $view, &$params)
    {
        // Load all special and non-string parameters
        // - the rest are fetched automatically
        $this->checked = (array_key_exists('checked', $params) ? $params['checked'] : false);

        $this->inputName = (array_key_exists('inputName', $params) ? $params['inputName'] : $this->id);
        $this->readOnly = (array_key_exists('readOnly', $params) ? $params['readOnly'] : false);

        $this->dataBased = (array_key_exists('dataBased', $params) ? $params['dataBased'] : true);
        $this->dataField = (array_key_exists('dataField', $params) ? $params['dataField'] : $this->id);
    }

    /**
     * Load event handler.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View render object.
     * @param array            &$params Parameters passed from the Smarty plugin function.
     *
     * @return void
     */
    public function load(Zikula_Form_View $view, &$params)
    {
        $this->loadValue($view, $view->get_template_vars());
    }

    /**
     * Load values.
     *
     * Called internally by the plugin itself to load values from the render.
     * Can also by called when some one is calling the render object's Zikula_Form_View::setValues.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_Viewr object.
     * @param array            &$values Values to load.
     *
     * @return void
     */
    public function loadValue(Zikula_Form_View $view, &$values)
    {
        if ($this->dataBased) {
            $value = null;

            if ($this->group == null) {
                if (array_key_exists($this->dataField, $values)) {
                    if (isset($values[$this->dataField])) {
                        $value = $values[$this->dataField];
                    } else {
                        $value = null;
                    }
                }
            } else {
                if (array_key_exists($this->group, $values)) {
                    if (isset($values[$this->group][$this->dataField])) {
                        $value = $values[$this->group][$this->dataField];
                    } else {
                        $value = null;
                    }
                }
            }

            if ($value !== null) {
                $this->checked = $value;
            }
        }
    }

    /**
     * Render event handler.
     *
     * @param Zikula_Form_View $view Reference to Form render object.
     *
     * @return string The rendered output
     */
    public function render(Zikula_Form_View $view)
    {
        $idHtml = $this->getIdHtml();

        $nameHtml = " name=\"{$this->inputName}\"";
        $readOnlyHtml = ($this->readOnly ? " disabled=\"disabled\"" : '');
        $checkedHtml = ($this->checked ? " checked=\"checked\"" : '');

        $class = 'z-form-checkbox';
        if ($this->cssClass != null) {
            $class .= ' ' . $this->cssClass;
        }

        $attributes = $this->renderAttributes($view);

        $result = "<input{$idHtml}{$nameHtml} type=\"checkbox\" value=\"1\" class=\"{$class}\"{$readOnlyHtml}{$checkedHtml}{$attributes} />";

        return $result;
    }

    /**
     * Decode event handler.
     *
     * @param Zikula_Form_View $view Reference to Form render object.
     *
     * @return void
     */
    public function decode(Zikula_Form_View $view)
    {
        // Do not read new value if readonly (evil submiter might have forged it)
        if (!$this->readOnly) {
            $this->checked = ($this->request->request->get($this->inputName, null) == null ? false : true);
        }
    }

    /**
     * Saves value in data object.
     *
     * Called by the render when doing $render->getValues()
     * Uses the group parameter to decide where to store data.
     *
     * @param Zikula_Form_View $view Reference to Form render object.
     * @param array            &$data Data object.
     *
     * @return void
     */
    public function saveValue(Zikula_Form_View $view, &$data)
    {
        if ($this->dataBased) {
            if ($this->group == null) {
                $data[$this->dataField] = $this->checked;
            } else {
                if (!array_key_exists($this->group, $data)) {
                    $data[$this->group] = array();
                }
                $data[$this->group][$this->dataField] = $this->checked;
            }
        }
    }
}
