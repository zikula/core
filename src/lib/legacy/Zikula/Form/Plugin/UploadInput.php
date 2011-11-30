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
 * Zikula_Form_Plugin_UploadInput
 */
class Zikula_Form_Plugin_UploadInput extends Zikula_Form_AbstractStyledPlugin
{
    /**
     * Input field name.
     *
     * @var string
     */
    public $inputName;

    /**
     * Whether or not this field is read only.
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
     * Field name in data array.
     *
     * @var string
     */
    public $dataField;

    /**
     * Whether or not to save $_FILES data in data array.
     *
     * @var boolean
     */
    public $dataBased;

    /**
     * Group to align data to.
     *
     * @var string
     */
    public $group;

    /**
     * Whether or not the field is valid.
     *
     * @var boolean
     */
    public $isValid;

    /**
     * Whether or not the field is mandatory.
     *
     * @var boolean
     */
    public $mandatory;

    /**
     * Holds the error message.
     *
     * @var string
     */
    public $errorMessage;

    /**
     * Holds the field's label.
     *
     * @var string
     */
    public $myLabel;

    /**
     * The result of the upload process (from $_FILES).
     *
     * @var array
     */
    public $result;

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
     * Checks whether the field is empty or not.
     *
     * @return boolean
     */
    function isEmpty()
    {
        return $this->result == null || $this->result['name'] == '';
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
        $this->inputName = (array_key_exists('inputName', $params) ? $params['inputName'] : $this->id);
        $this->readOnly = (array_key_exists('readOnly', $params) ? $params['readOnly'] : false);
        $this->mandatory = (array_key_exists('mandatory', $params) ? $params['mandatory'] : false);

        $this->dataBased = (array_key_exists('dataBased', $params) ? $params['dataBased'] : true);
        $this->dataField = (array_key_exists('dataField', $params) ? $params['dataField'] : $this->id);
        $this->group = (array_key_exists('group', $params) ? $params['group'] : null);

        $this->result = null;
        $this->isValid = true;
    }

    /**
     * Initialize event handler.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     *
     * @return void
     */
    function initialize(Zikula_Form_View $view)
    {
        $view->addValidator($this);
    }

    /**
     * Render event handler.
     *
     * @param Zikula_Form_View $view Reference to Form render object.
     *
     * @return string The rendered output
     */
    function render(Zikula_Form_View $view)
    {
        $idHtml = $this->getIdHtml();
        $nameHtml = " name=\"{$this->inputName}\"";
        $readOnlyHtml = ($this->readOnly ? " readonly=\"readonly\"" : '');

        $class = 'z-form-upload';
        if (!$this->isValid) {
            $class .= ' z-form-error';
        }
        if ($this->readOnly) {
            $class .= ' z-form-readonly';
        }
        if ($this->cssClass != null) {
            $class .= ' ' . $this->cssClass;
        }

        $titleHtml = ($this->errorMessage != null ? " title=\"{$this->errorMessage}\"" : '');
        $attributes = $this->renderAttributes($view);
        $result = "<input{$idHtml}{$nameHtml} type=\"file\" class=\"{$class}\"{$readOnlyHtml}{$titleHtml}{$attributes} />";

        return $result;
    }

    /**
     * Decode event handler.
     *
     * @param Zikula_Form_View $view Reference to Form render object.
     *
     * @return void
     */
    function decode(Zikula_Form_View $view)
    {
        if (isset($_FILES[$this->inputName])) {
            $this->result = $_FILES[$this->inputName];
        }
    }

    /**
     * Validates the input.
     *
     * @param Zikula_Form_View $view Reference to Form render object.
     *
     * @return void
     */
    function validate(Zikula_Form_View $view)
    {
        $this->clearValidation($view);

        if (isset($this->result['error']) && $this->result['error'] != 0 && $this->result['name'] != '') {
            $this->setError(__('Error! Did not succeed in uploading file.'));
        }

        if ($this->mandatory && $this->isEmpty() && !isset($this->upl_arr['orig_name'])) {
            $this->setError(__('Error! An entry in this field is mandatory.'));
        }
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
        $this->toolTip = $msg;
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
                $data[$this->dataField] = $this->result;
            } else {
                if (!array_key_exists($this->group, $data)) {
                    $data[$this->group] = array();
                }
                $data[$this->group][$this->dataField] = $this->result;
            }
        }
    }
}
