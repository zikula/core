<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Form
 * @subpackage Form_Plugin
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Form_Plugin_UploadInput
 */
class Form_Plugin_UploadInput extends Form_StyledPlugin
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
     * @param Form_View $render Reference to Form render object.
     * @param array       $params  Parameters passed from the Smarty plugin function.
     *
     * @see    Form_Plugin
     * @return void
     */
    function create($render, $params)
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
     * @param FormRender $render Reference to Form_View object.
     *
     * @return void
     */
    function initialize($render)
    {
        $render->addValidator($this);
    }

    /**
     * Render event handler.
     *
     * @param Form_View $render Reference to Form render object.
     *
     * @return string The rendered output
     */
    function render($render)
    {
        $idHtml = $this->getIdHtml();
        $nameHtml = " name=\"{$this->inputName}\"";
        $readOnlyHtml = ($this->readOnly ? " readonly=\"readonly\"" : '');

        $class = 'upload';
        if (!$this->isValid) {
            $class .= ' error';
        }

        if ($this->readOnly) {
            $class .= ' readonly';
        }

        $titleHtml = ($this->errorMessage != null ? " title=\"$this->errorMessage\"" : '');
        $attributes = $this->renderAttributes($render);
        $result = "<input type=\"file\" class=\"$class\"{$idHtml}{$nameHtml}{$readOnlyHtml}{$titleHtml}{$attributes}/>";

        return $result;
    }

    /**
     * Decode event handler.
     *
     * @param Form_View $render Reference to Form render object.
     *
     * @return void
     */
    function decode($render)
    {
        $this->result = $_FILES[$this->inputName];
    }

    /**
     * Validates the input.
     *
     * @param Form_View $render Reference to Form render object.
     *
     * @return void
     */
    function validate($render)
    {
        $this->clearValidation($render);

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
     * @param Form_View $render Reference to Form render object.
     *
     * @return void
     */
    function clearValidation($render)
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
     * @param Form_View $render Reference to Form render object.
     * @param array       &$data   Data object.
     *
     * @return void
     */
    function saveValue($render, &$data)
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

