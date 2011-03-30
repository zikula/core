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
 * Base implementation for checkbox and dropdown list
 */
class Zikula_Form_Plugin_BaseListSelector extends Zikula_Form_AbstractStyledPlugin
{
    /**
     * Enable or disable read only mode.
     *
     * @var boolean
     */
    public $readOnly;

    /**
     * CSS class for styling.
     *
     * @var string
     */
    public $cssClass;

    /**
     * Data field name for looking up initial data.
     *
     * The name stored here is used to lookup initial data for the plugin in the render's variables.
     * The value itself depends on the plugin that extends this base class.
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
     * Data field name for looking up initial item list.
     *
     * The name stored here is used to lookup initial item list in the render's variables.
     * The value should be an array as described for the $items variable.
     * Defaults to the data field name concatenated with "Items". See also tutorials on the Zikula site.
     *
     * @var string
     */
    public $itemsDataField;

    /**
     * Validation indicator used by the framework.
     *
     * The true/false value of this variable indicates whether or not the list selection is valid.
     *
     * @var boolean
     */
    public $isValid = true;

    /**
     * Enable or disable mandatory check.
     *
     * @var boolean
     */
    public $mandatory;

    /**
     * Error message to display when selection does not validate.
     *
     * @var string
     */
    public $errorMessage;

    /**
     * Text label for this plugin.
     *
     * This variable contains the label text for the input. The {@link Zikula_Form_Plugin_Label} plugin will set
     * this text automatically when it is a label for this list.
     *
     * @var string
     */
    public $myLabel;

    /**
     * The list of selectable items.
     *
     * This is an array of arrays like this:
     * array( array('text' => 'A', 'value' => '1'),
     * array('text' => 'B', 'value' => '2'),
     * array('text' => 'C', 'value' => '3') )
     *
     * @var array
     */
    public $items = array();

    /**
     * HTML input name for this plugin. Defaults to the ID of the plugin.
     *
     * @var string
     */
    public $inputName;

    /**
     * Get filename of this file.
     *
     * @return string
     */
    function getFilename()
    {
        return __FILE__;
    }

    /**
     * Create event handler.
     *
     * @param Zikula_Form_View $view    Reference to Form render object.
     * @param array            &$params Parameters passed from the Smarty plugin function.
     *
     * @see    Zikula_Form_AbstractPlugin
     * @return void
     */
    function create(Zikula_Form_View $view, &$params)
    {
        $this->inputName = $this->id;

        $this->readOnly = (array_key_exists('readOnly', $params) ? $params['readOnly'] : false);

        $this->dataBased = (array_key_exists('dataBased', $params) ? $params['dataBased'] : true);
        $this->dataField = (array_key_exists('dataField', $params) ? $params['dataField'] : $this->id);
        $this->itemsDataField = (isset($params['itemsDataField'])) ? $params['itemsDataField'] : $this->id . 'Items';

        $this->isValid = true;
        $this->mandatory = (array_key_exists('mandatory', $params) ? $params['mandatory'] : false);
    }

    /**
     * Initialize event handler.
     *
     * @param Zikula_Form_View $view Reference to Form render object.
     *
     * @return void
     */
    function initialize(Zikula_Form_View $view)
    {
        $view->addValidator($this);
    }

    /**
     * Load event handler.
     *
     * @param Zikula_Form_View $view    Reference to Zikula_Form_View object.
     * @param array            &$params Parameters passed from the Smarty plugin function.
     *
     * @return void
     */
    function load(Zikula_Form_View $view, &$params)
    {
        // The load function expects the plugin to read values from the render.
        // This can be done with the loadValue function (which can be called in other situations than
        // through the onLoad event).
        $this->loadValue($view, $view->get_template_vars());
    }

    /**
     * Sets an error message.
     *
     * @param string $msg Error message.
     *
     * @return void
     */
    function setError($msg)
    {
        $this->isValid = false;
        $this->errorMessage = $msg;
    }

    /**
     * Clears the validation data.
     *
     * @param Zikula_Form_View $view Reference to Form render object.
     *
     * @return void
     */
    function clearValidation(Zikula_Form_View $view)
    {
        $this->isValid = true;
        $this->errorMessage = null;
    }

    /**
     * Saves value in data object.
     *
     * Called by the render when doing $render->getValues()
     * Uses the group parameter to decide where to store data.
     *
     * @param Zikula_Form_View $view  Reference to Form render object.
     * @param array            &$data Data object.
     *
     * @return void
     */
    function saveValue(Zikula_Form_View $view, &$data)
    {
        if ($this->dataBased) {
            if ($this->group == null) {
                $data[$this->dataField] = $this->getSelectedValue();
            } else {
                if (!array_key_exists($this->group, $data)) {
                    $data[$this->group] = array();
                }
                $data[$this->group][$this->dataField] = $this->getSelectedValue();
            }
        }
    }

    /**
     * Load values.
     *
     * Called internally by the plugin itself to load values from the render.
     * Can also by called when some one is calling the render object's Zikula_Form_View::setValues.
     *
     * @param Zikula_Form_View $view    Reference to Zikula_Form_View object.
     * @param array            &$values Values to load.
     *
     * @return void
     */
    function loadValue(Zikula_Form_View $view, &$values)
    {
        if ($this->dataBased) {
            $items = null;
            $value = null;

            if ($this->group == null) {
                if ($this->dataField != null && isset($values[$this->dataField])) {
                    $value = $values[$this->dataField];
                }
                if ($this->itemsDataField != null && isset($values[$this->itemsDataField])) {
                    $items = $values[$this->itemsDataField];
                }
            } elseif (isset($values[$this->group])) {
                $data = $values[$this->group];
                if (isset($data[$this->dataField])) {
                    $value = $data[$this->dataField];
                }
                if ($this->itemsDataField != null && isset($data[$this->itemsDataField])) {
                    $items = $data[$this->itemsDataField];
                }
            }

            if ($items !== null) {
                $this->setItems($items);
            }

            $this->setSelectedValue($value);
        }
    }

    /**
     * Set the selected value.
     *
     * To be implemented by extending class.
     *
     * @param mixed $value Selected value.
     *
     * @return boolean
     */
    function setSelectedValue($value)
    {
        return true;
    }

    /**
     * Get the selected value.
     *
     * To be implemented by extending class.
     *
     * @return mixed The selected value.
     */
    function getSelectedValue()
    {
        return null;
    }

    /**
     * Add item to list.
     *
     * @param string $text  The text of the item.
     * @param string $value The value of the item.
     *
     * @return void
     */
    function addItem($text, $value)
    {
        $item = array(
            'text' => $text,
            'value' => $value
        );

        $this->items[] = $item;
    }

    /**
     * Add several items to list.
     *
     * Quicker than copying the items one by one.
     * If addItem() does som special logic in the future then call that for each element in $items.
     *
     * @param array $items List of items.
     *
     * @return void
     */
    function setItems($items)
    {
        $this->items = $items;
    }
}
