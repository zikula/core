<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Check box plugin
 *
 * Plugin to generate a checkbox for true/false selection.
 *
 * @deprecated for Symfony2 Forms
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
     * Enable or disable mandatory check.
     *
     * By enabling mandatory checking you force the user to enter something in the text input.
     *
     * @var boolean
     */
    public $mandatory;

    /**
     * Enable or disable mandatory asterisk.
     *
     * @var boolean
     */
    public $mandatorysym;

    /**
     * Error message to display when input does not validate.
     *
     * Use {@link Zikula_Form_Plugin_Checkbox::setError()} and {@link Zikula_Form_Plugin_Checkbox::clearValidation()}
     * to change the value.
     *
     * @var string
     */
    public $errorMessage;

    /**
     * Text label for this plugin.
     *
     * This variable contains the label text for the input. The {@link Zikula_Form_Plugin_Label} plugin will set
     * this text automatically when it is a label for this input.
     *
     * @var string
     */
    public $myLabel;

    /**
     * Text to show as tool tip for the input.
     *
     * @var string
     */
    public $toolTip;

    /**
     * Validation indicator used by the framework.
     *
     * The true/false value of this variable indicates whether or not the text input is valid
     * (a valid input satisfies the mandatory requirement and regex validation pattern).
     * Use {@link Zikula_Form_Plugin_TextInput::setError()} and {@link Zikula_Form_Plugin_TextInput::clearValidation()}
     * to change the value.
     *
     * @var boolean
     */
    public $isValid = true;

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
     * @param Zikula_Form_View $view Reference to Form render object
     * @param array            &$params Parameters passed from the Smarty plugin function
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
     * @param Zikula_Form_View $view Reference to Zikula_Form_View render object
     * @param array            &$params Parameters passed from the Smarty plugin function
     *
     * @return void
     */
    public function load(Zikula_Form_View $view, &$params)
    {
        $this->loadValue($view, $view->get_template_vars());
    }

    /**
     * Initialize event handler.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object
     *
     * @return void
     */
    public function initialize(Zikula_Form_View $view)
    {
        $view->addValidator($this);
    }

    /**
     * Load values.
     *
     * Called internally by the plugin itself to load values from the render.
     * Can also by called when some one is calling the render object's Zikula_Form_View::setValues.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_Viewr object
     * @param array            &$values Values to load
     *
     * @return void
     */
    public function loadValue(Zikula_Form_View $view, &$values)
    {
        if ($this->dataBased) {
            $value = null;

            if (null == $this->group) {
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

            if (null !== $value) {
                $this->checked = $value;
            }
        }
    }

    /**
     * Render event handler.
     *
     * @param Zikula_Form_View $view Reference to Form render object
     *
     * @return string The rendered output
     */
    public function render(Zikula_Form_View $view)
    {
        $idHtml = $this->getIdHtml();

        $nameHtml = " name=\"{$this->inputName}\"";
        $titleHtml = (null != $this->toolTip ? ' title="' . $view->translateForDisplay($this->toolTip) . '"' : '');
        $readOnlyHtml = ($this->readOnly ? " disabled=\"disabled\"" : '');
        $checkedHtml = ($this->checked ? " checked=\"checked\"" : '');

        $class = $this->getStyleClass();

        $attributes = $this->renderAttributes($view);

        $result = "<input{$idHtml}{$nameHtml}{$titleHtml} type=\"checkbox\" value=\"1\" class=\"{$class}\"{$readOnlyHtml}{$checkedHtml}{$attributes} />";

        if ($this->mandatory && $this->mandatorysym) {
            $result .= '<span class="z-form-mandatory-flag">*</span>';
        }

        return $result;
    }

    /**
     * Decode event handler.
     *
     * @param Zikula_Form_View $view Reference to Form render object
     *
     * @return void
     */
    public function decode(Zikula_Form_View $view)
    {
        // Do not read new value if readonly (evil submiter might have forged it)
        if (!$this->readOnly) {
            $this->checked = (null == $this->request->request->get($this->inputName, null) ? false : true);
        }
    }

    /**
     * Saves value in data object.
     *
     * Called by the render when doing $render->getValues()
     * Uses the group parameter to decide where to store data.
     *
     * @param Zikula_Form_View $view Reference to Form render object
     * @param array            &$data Data object
     *
     * @return void
     */
    public function saveValue(Zikula_Form_View $view, &$data)
    {
        if ($this->dataBased) {
            if (null == $this->group) {
                $data[$this->dataField] = $this->checked;
            } else {
                if (!array_key_exists($this->group, $data)) {
                    $data[$this->group] = [];
                }
                $data[$this->group][$this->dataField] = $this->checked;
            }
        }
    }

    /**
     * Validates the input.
     *
     * @param Zikula_Form_View $view Zikula_Form_View object
     *
     * @return void
     */
    public function validate(Zikula_Form_View $view)
    {
        $this->clearValidation($view);

        if ($this->mandatory && !$this->checked) {
            $this->setError(__('Error! You must check this checkbox.'));
        }
    }

    /**
     * Sets an error message.
     *
     * @param string $msg Error message
     *
     * @return void
     */
    public function setError($msg)
    {
        $this->isValid = false;
        $this->errorMessage = $msg;
        $this->toolTip = $msg;
    }

    /**
     * Clears the validation data.
     *
     * @param Zikula_Form_View $view Zikula_Form_View object
     *
     * @return void
     */
    public function clearValidation(Zikula_Form_View $view)
    {
        $this->isValid = true;
        $this->errorMessage = null;
        $this->toolTip = null;
    }

    /**
     * Helper method to determine css class.
     *
     * Can be overridden by subclasses like Zikula_Form_Plugin_IntInput and Zikula_Form_Plugin_FloatInput.
     *
     * @todo Customize styles for checkboxes
     *
     * @return string the list of css classes to apply
     */
    protected function getStyleClass()
    {
        $class = 'z-form-checkbox';
        if (!$this->isValid) {
            $class .= ' z-form-error';
        }
        if ($this->mandatory && $this->mandatorysym) {
            $class .= ' z-form-mandatory';
        }
        if ($this->readOnly) {
            $class .= ' z-form-readonly';
        }
        if (null != $this->cssClass) {
            $class .= ' ' . $this->cssClass;
        }

        return $class;
    }
}
