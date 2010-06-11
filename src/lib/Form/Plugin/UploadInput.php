<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
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
    public $inputName;
    public $readOnly;

    public $dataField;
    public $dataBased;
    public $group;

    public $isValid;
    public $mandatory;
    public $errorMessage;
    public $myLabel;

    public $result;

    function getFilename()
    {
        return __FILE__;
    }

    function isEmpty()
    {
        return $this->result == null || $this->result['name'] == '';
    }

    function create(&$render, $params)
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

    function initialize(&$render)
    {
        $render->addValidator($this);
    }

    function render(&$render)
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

    function decode(&$render)
    {
        $this->result = $_FILES[$this->inputName];
    }

    function validate(&$render)
    {
        $this->clearValidation($render);

        if (isset($this->result['error']) && $this->result['error'] != 0 && $this->result['name'] != '') {
            $this->setError(__('Error! Did not succeed in uploading file.'));
        }

        if ($this->mandatory && $this->isEmpty() && !isset($this->upl_arr['orig_name'])) {
            $this->setError(__('Error! An entry in this field is mandatory.'));
        }
    }

    function setError($msg)
    {
        $this->isValid = false;
        $this->errorMessage = $msg;
        $this->toolTip = $msg;
    }

    function clearValidation(&$render)
    {
        $this->isValid = true;
        $this->errorMessage = null;
    }

    function saveValue(&$render, &$data)
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

