<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * TextInput plugin for pnForms
 *
 * The pnFormTextInput plugin is a general purpose input plugin that allows the user to enter any kind of character based data,
 * including text, numbers, dates and more.
 *
 * Typical use in template file:
 * <code>
 * <!--[formtextinput id="title" maxLength="100" width="30em"]-->
 * </code>
 *
 * The pnFormTextInput plugin supports basic CSS styling through attributes like "width", "color" and "font_weight". See
 * {@link pnFormStyledPlugin} for more info.
 */
class Form_Plugin_TextInput extends Form_StyledPlugin
{
    /**
     * Displayed text in the text input
     *
     * This variable contains the text to be displayed in the input.
     * At first page display this variable contains whatever set in the template. At postback it contains
     * user input. User input is always trimmed using the HPH function trim().
     *
     * @var string
     */
    public $text = '';

    /**
     * Text input mode
     *
     * The text mode defines what kind of HTML element to render. The possible values are:
     * - <b>Singleline</b>: renders a normal input element (default).
     * - <b>Password</b>: renders a input element of type "password" (means you cannot read what you are typing).
     * - <b>Multiline</b>: renders a textarea element.
     *
     * @var string
     */
    public $textMode = 'singleline';

    /**
     * Enable or disable read only mode
     *
     * A text input in read only mode do *not* decode posted data, since the user cannot
     * enter anything in the read only input.
     * @var bool
     */
    public $readOnly;

    /**
     * Text to show as tool tip for the input
     * @var string
     */
    public $toolTip;

    /**
     * CSS class to use
     * @var string
     */
    public $cssClass;

    /**
     * Number of columns for multiline input
     * @var int
     */
    public $cols;

    /**
     * Number of rows for multiline input
     * @var int
     */
    public $rows;

    /**
     * Data field name for looking up initial data
     *
     * The name stored here is used to lookup initial data for the plugin in the render's variables.
     * Defaults to the ID of the plugin. See also tutorials on the Zikula site.
     * @var string
     */
    public $dataField;

    /**
     * Enable or disable use of $dataField
     * @var bool
     */
    public $dataBased;

    /**
     * Group name for this input
     *
     * The group name is used to locate data in the render (when databased) and to restrict which
     * plugins to do validation on (to be implemented).
     * @see pnFormRender::pnFormGetValues()
     * @see pnFormRender::pnFormIsValid()
     * @var string
     */
    public $group;

    /**
     * Validation indicator used by the framework.
     *
     * The true/false value of this variable indicates whether or not the text input is valid
     * (a valid input satisfies the mandatory requirement and regex validation pattern).
     * Use {@link pnFormTextInput::setError()} and {@link pnFormTextInput::clearValidation()}
     * to change the value.
     * @var bool
     */
    public $isValid = true;

    /**
     * Enable or disable mandatory check
     *
     * By enabling mandatory checking you force the user to enter something in the text input.
     * @var bool
     */
    public $mandatory;

    /**
     * Enable or disable mandatory asterisk
     * @var bool
     */
    public $mandatorysym;

    /**
     * Error message to display when input does not validate
     *
     * Use {@link pnFormTextInput::setError()} and {@link pnFormTextInput::clearValidation()}
     * to change the value.
     * @var string
     */
    public $errorMessage;

    /**
     * Text label for this plugin
     *
     * This variable contains the label text for the input. The {@link pnFormLabel} plugin will set
     * this text automatically when it is a label for this input.
     * @var string
     */
    public $myLabel;

    /**
     * Size of HTML input (number of characters)
     * @var int
     */
    public $size;

    /**
     * Maximum number of characters allowed in the text input
     * @var int
     */
    public $maxLength;

    /**
     * Regular expression to match input against
     *
     * User input must match this pattern. Uses PHP preg_match() to match the input and pattern.
     * @var string
     */
    public $regexValidationPattern;

    /**
     * Regular expression error message
     *
     * Error message to display when the regex validation pattern does not match input.
     * @var string
     */
    public $regexValidationMessage;

    /**
     * HTML input name for this plugin. Defaults to the ID of the plugin.
     * @var string
     */
    public $inputName;

    /**
     * Get filename for this plugin
     *
     * A requirement from the framework - must be implemented like this. Used to restore plugins on postback.
     * @internal
     * @return string
     */
    function getFilename()
    {
        return __FILE__;
    }

    /**
     * Indicates whether or not the input is empty
     *
     * @return bool
     */
    function isEmpty()
    {
        return $this->text == '';
    }

    function create(&$render, $params)
    {
        // All member variables are fetched automatically before create (as strings)
        // Here we afterwards load all special and non-string parameters
        $this->inputName = (array_key_exists('inputName', $params) ? $params['inputName'] : $this->id);
        $this->textMode = (array_key_exists('textMode', $params) ? $params['textMode'] : 'singleline');

        $this->dataField = (array_key_exists('dataField', $params) ? $params['dataField'] : $this->id);
        $this->dataBased = (array_key_exists('dataBased', $params) ? $params['dataBased'] : true);

        if (array_key_exists('maxLength', $params)) {
            $this->maxLength = $params['maxLength'];
        } else if ($this->maxLength == null && strtolower($this->textMode) != 'multiline') {
            $render->FormDie("Missing maxLength value in textInput plugin '$this->id'.");
        }
    }

    function load(&$render, &$params)
    {
        // The load function expects the plugin to read values from the render.
        // This can be done with the loadValue function (which can be called in other situations than
        // through the onLoad event).
        $this->loadValue($render, $render->get_template_vars());
    }

    function initialize(&$render)
    {
        $render->AddValidator($this);
    }

    function render(&$render)
    {
        $idHtml = $this->getIdHtml();

        $nameHtml = " name=\"{$this->inputName}\"";

        $titleHtml = ($this->toolTip != null ? ' title="' . $render->TranslateForDisplay($this->toolTip) . '"' : '');

        $readOnlyHtml = ($this->readOnly ? ' readonly="readonly" tabindex="-1"' : '');

        $sizeHtml = ($this->size > 0 ? " size=\"$this->size\"" : '');

        $maxLengthHtml = ($this->maxLength > 0 ? " maxlength=\"$this->maxLength\"" : '');

        $text = DataUtil::formatForDisplay($this->text);

        $class = 'text';
        if (!$this->isValid) {
            $class .= ' error';
        }
        if ($this->mandatory && $this->mandatorysym) {
            $class .= ' z-mandatoryinput';
        }
        if ($this->readOnly) {
            $class .= ' readonly';
        }
        if ($this->cssClass != null) {
            $class .= ' ' . $this->cssClass;
        }

        $attributes = $this->renderAttributes($render);

        switch (strtolower($this->textMode)) {
            case 'singleline':
                $result = "<input type=\"text\"{$idHtml}{$nameHtml}{$titleHtml}{$sizeHtml}{$maxLengthHtml}{$readOnlyHtml} class=\"$class\" value=\"{$text}\"$attributes/>";
                if ($this->mandatory && $this->mandatorysym)
                    $result .= '<span class="z-mandatorysym">*</span>';
                break;

            case 'password':
                $result = "<input type=\"password\"{$idHtml}{$nameHtml}{$titleHtml}{$maxLengthHtml}{$readOnlyHtml} class=\"$class\" value=\"{$text}\"$attributes/>";
                if ($this->mandatory && $this->mandatorysym)
                    $result .= '<span class="z-mandatorysym">*</span>';
                break;

            case 'multiline':
                $colsrowsHtml = '';
                if ($this->cols != null) {
                    $colsrowsHtml .= " cols=\"$this->cols\"";
                }

                if ($this->rows != null) {
                    $colsrowsHtml .= " rows=\"$this->rows\"";
                }

                $result = "<textarea{$idHtml}{$nameHtml}{$titleHtml}{$readOnlyHtml}{$colsrowsHtml} class=\"$class\"$attributes>$text</textarea>";
                if ($this->mandatory && $this->mandatorysym) {
                    $result .= '<span class="z-mandatorysym">*</span>';
                }
                break;

            default:
                $result = "UNKNOWN TEXTMODE $this->textMode.";
        }

        return $result;
    }

    function decode(&$render)
    {
        // Do not read new value if readonly (evil submiter might have forged it)
        if (!$this->readOnly) {
            $this->text = FormUtil::getPassedValue($this->inputName, null, 'POST');
            if (get_magic_quotes_gpc()) {
                $this->text = stripslashes($this->text);
            }

            // Make sure newlines are returned as "\n" - always.
            $this->text = str_replace("\r\n", "\n", $this->text);
            $this->text = str_replace("\r", "\n", $this->text);

            $this->text = trim($this->text);
        }
    }

    function validate(&$render)
    {
        $this->clearValidation($render);

        if ($this->mandatory && $this->isEmpty()) {
            $this->setError(__('Error! An entry in this field is mandatory.'));
        } else if (strlen($this->text) > $this->maxLength && $this->maxLength > 0) {
            $this->setError(sprintf(__('Error! Input text must be no longer than %s characters.'), $this->maxLength));
        } else if ($this->regexValidationPattern != null && $this->text != '' && !preg_match($this->regexValidationPattern, $this->text)) {
            $this->setError($render->TranslateForDisplay($this->regexValidationMessage));
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
        $this->toolTip = null;
    }

    // Called by the render when doing $render->GetValues()
    // Uses the group parameter to decide where to store data.
    function saveValue(&$render, &$data)
    {
        if ($this->dataBased) {
            $value = $this->parseValue($render, $this->text);

            if ($this->group == null) {
                $data[$this->dataField] = $value;
            } else {
                if (!array_key_exists($this->group, $data)) {
                    $data[$this->group] = array();
                }
                $data[$this->group][$this->dataField] = $value;
            }
        }
    }

    // Override this function in inherited plugins if other format is needed
    function parseValue(&$render, $text)
    {
        return $text;
    }

    // Called internally by the plugin itself to load values from the render.
    // Can also by called when some one is calling the render object's pnFormSetValues
    function loadValue(&$render, &$values)
    {
        if ($this->dataBased) {
            $value = null;

            if ($this->group == null) {
                if (array_key_exists($this->dataField, $values)) {
                    $value = $values[$this->dataField];
                }
            } else {
                if (array_key_exists($this->group, $values) && is_array($values[$this->group])) {
                    if (array_key_exists($this->dataField, $values[$this->group])) {
                        $value = $values[$this->group][$this->dataField];
                    }
                }
            }

            if ($value !== null) {
                $this->text = $this->formatValue($render, $value);
            }
        }
    }

    // Override this function in inherited plugins if other format is needed
    function formatValue(&$render, $value)
    {
        return $value;
    }
}

