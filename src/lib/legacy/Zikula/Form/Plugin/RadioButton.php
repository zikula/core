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
 * Radiobutton plugin
 *
 * Plugin to generate a radiobutton for selecting one-of-X.
 * Usage with fixed number of radiobuttons:
 *
 * <code>
 * {formradiobutton id='yesButton' dataField='ok'} {formlabel __text='Yes' for='yesButton'} <br/>
 * {formradiobutton id='noButton' dataField='ok'} {formlabel __text='No' for='noButton'}
 * </code>
 *
 * The above case sets 'ok' to either 'yesButton' or 'noButton' in the hashtable returned
 * by {@link Zikula_Form_View::getValues()}. As you can see the radiobutton defaults to using the ID for the returned value
 * in the hashtable. You can override this by setting 'value' to something different.
 *
 * You can also enforce a selection:
 *
 * <code>
 * {formradiobutton id='yesButton' dataField='ok' mandatory=true} {formlabel __text='Yes' for='yesButton'} <br/>
 * {formradiobutton id='noButton' dataField='ok' mandatory=true} {formlabel __text='No' for='noButton'}
 * </code>
 *
 * If you have a list of radiobuttons inside a for/each loop then you can set the ID to something from the data loop
 * like here:
 * <code>
 * {foreach from=$items item=item}
 *   {formradiobutton id=$item.name dataField='item' mandatory=true} {formlabel text=$item.title for=$item.name}
 * {/foreach}
 * </code>
 *
 * @deprecated for Symfony2 Forms
 */
class Zikula_Form_Plugin_RadioButton extends Zikula_Form_AbstractStyledPlugin
{
    /**
     * The value returned in Zikula_Form_View::getValues() when this radio button is checked.
     *
     * @var string
     */
    public $value;

    /**
     * The current state of the radio button.
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
     *
     * @see   Zikula_Form_View::getValues(), Zikula_Form_View::isValid()
     */
    public $group;

    /**
     * Radiobutton selection group name.
     *
     * @var string
     */
    public $groupName;

    /**
     * Validation indicator used by the framework.
     *
     * The true/false value of this variable indicates whether or not radiobutton selection is valid
     * (a valid (set of) radiobuttons satisfies the mandatory requirement).
     * Use {@link Zikula_Form_Plugin_RadioButton::setError()} and {@link Zikula_Form_Plugin_RadioButton::clearValidation()}
     * to change the value.
     *
     * @var boolean
     */
    public $isValid = true;

    /**
     * Enable or disable mandatory check.
     *
     * By enabling mandatory checking you force the user to check one of the radio buttons on the page
     * that shares the same groupName.
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
     * Enable or disable auto postback.
     *
     * Auto postback means "generate a server side event when selection changes".
     * If enabled then the event handler named in $onSelectedIndexChanged will be fired
     * in the main form event handler.
     *
     * @var boolean
     */
    public $autoPostBack;

    /**
     * Name of checked changed method.
     *
     * @var string Default is "handleCheckedChanged"
     */
    public $onCheckedChanged = 'handleCheckedChanged';

    /**
     * Error message to display when input does not validate.
     *
     * Use {@link Zikula_Form_Plugin_RadioButton::setError()} and {@link Zikula_Form_Plugin_RadioButton::clearValidation()}
     * to change the value.
     *
     * @var string
     */
    public $errorMessage;

    /**
     * Text label for this plugin.
     *
     * This variable contains the label text for the radiobutton. The {@link Zikula_Form_Plugin_Label} plugin will set
     * this text automatically when it is a label for this input.
     *
     * @var string
     */
    public $myLabel;

    /**
     * Whether or not a radio button of the group is checked.
     *
     * @var boolean
     */
    public $validationChecked = false;

    /**
     * Get filename of this file.
     *
     * @return string
     */
    public function getFilename()
    {
        return __FILE__;
    }

    /**
     * Create event handler.
     *
     * @param Zikula_Form_View $view Zikula_Form_View object.
     * @param array            &$params Parameters passed from the Smarty plugin function.
     *
     * @see    Zikula_Form_AbstractPlugin
     *
     * @return void
     */
    public function create(Zikula_Form_View $view, &$params)
    {
        // Load all special and non-string parameters
        // - the rest are fetched automatically
        $this->checked = (array_key_exists('checked', $params) ? $params['checked'] : false);

        $this->readOnly = (array_key_exists('readOnly', $params) ? $params['readOnly'] : false);

        $this->dataBased = (array_key_exists('dataBased', $params) ? $params['dataBased'] : true);
        $this->value = (string)(array_key_exists('value', $params) ? $params['value'] : $this->id);
        $this->groupName = (array_key_exists('groupName', $params) ? $params['groupName'] : $this->dataField);
    }

    /**
     * Load event handler.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
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
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
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
                    $value = (string)$values[$this->dataField];
                }
            } else {
                if (array_key_exists($this->group, $values) && array_key_exists($this->dataField, $values[$this->group])) {
                    $value = (string)$values[$this->group][$this->dataField];
                }
            }

            if ($value !== null) {
                $this->checked = ($this->value === $value);
            } else {
                $this->checked = false;
            }
        }
    }

    /**
     * Initialize event handler.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     *
     * @return void
     */
    public function initialize(Zikula_Form_View $view)
    {
        $this->validationChecked = false;
        $view->addValidator($this);
    }

    /**
     * Render event handler.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     *
     * @return string The rendered output
     */
    public function render(Zikula_Form_View $view)
    {
        $idHtml = $this->getIdHtml();

        $nameHtml = " name=\"{$this->groupName}\"";
        $readOnlyHtml = ($this->readOnly ? " disabled=\"disabled\"" : '');
        $checkedHtml = ($this->checked ? " checked=\"checked\"" : '');

        $postbackHtml = '';
        if ($this->autoPostBack) {
            $postbackHtml = " onclick=\"" . $view->getPostBackEventReference($this, '') . "\"";
        }

        $class = 'z-form-radio';
        if ($this->mandatory && $this->mandatorysym) {
            $class .= ' z-form-mandatory';
        }
        if ($this->readOnly) {
            $class .= ' z-form-readonly';
        }
        if ($this->cssClass != null) {
            $class .= ' ' . $this->cssClass;
        }

        $attributes = $this->renderAttributes($view);

        $result = "<input{$idHtml}{$nameHtml} type=\"radio\" value=\"{$this->value}\"{$readOnlyHtml}{$checkedHtml}{$postbackHtml}{$attributes} class=\"{$class}\" />";
        if ($this->mandatory && $this->mandatorysym) {
            $result .= '<span class="z-form-mandatory-flag">*</span>';
        }

        return $result;
    }

    /**
     * Called by Zikula_Form_View framework due to the use of Zikula_Form_View::getPostBackEventReference() above.
     *
     * @param Zikula_Form_View $view          Reference to Zikula_Form_View object.
     * @param string           $eventArgument The event argument.
     *
     * @return void
     */
    public function raisePostBackEvent(Zikula_Form_View $view, $eventArgument)
    {
        $args = [
            'commandName' => null,
            'commandArgument' => null
        ];
        if (!empty($this->onCheckedChanged)) {
            $view->raiseEvent($this->onCheckedChanged, $args);
        }
    }

    /**
     * Decode event handler.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     *
     * @return void
     */
    public function decode(Zikula_Form_View $view)
    {
        // Do not read new value if readonly (evil submiter might have forged it)
        if (!$this->readOnly) {
            $this->checked = ($this->request->request->get($this->groupName, null) === $this->value ? true : false);
        }
    }

    /**
     * Validates the input.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     *
     * @return void
     */
    public function validate(Zikula_Form_View $view)
    {
        $this->clearValidation($view);

        if ($this->mandatory && !$this->validationChecked) {
            $firstRadioButton = null;
            if (!$this->findCheckedRadioButton($view, $firstRadioButton)) {
                $this->setError(__('Error! You must make a selection.'));
            }
        }
    }

    /**
     * Find the checked radio button in group.
     *
     * @param Zikula_Form_View               $view             Reference to Zikula_Form_View object.
     * @param Zikula_Form_Plugin_RadioButton $firstRadioButton The first found radio button.
     *
     * @return boolean
     */
    public function findCheckedRadioButton(Zikula_Form_View $view, $firstRadioButton)
    {
        $lim = count($view->plugins);

        for ($i = 0; $i < $lim; ++$i) {
            if ($this->findCheckedRadioButton_rec($firstRadioButton, $view->plugins[$i])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Recursive helper method for self::findCheckedRadioButton().
     *
     * @param Zikula_Form_Plugin_RadioButton $firstRadioButton The first found radio button.
     * @param Zikula_Form_Plugin             $plugin           A Form plugin.
     *
     * @return boolean
     */
    public function findCheckedRadioButton_rec($firstRadioButton, $plugin)
    {
        if ($plugin instanceof self && $plugin->groupName == $this->groupName) {
            $plugin->validationChecked = true;
            if ($firstRadioButton == null) {
                $firstRadioButton = $plugin;
            }
            if ($plugin->checked) {
                return true;
            }
        }

        $lim = count($plugin->plugins);

        for ($i = 0; $i < $lim; ++$i) {
            if ($this->findCheckedRadioButton_rec($firstRadioButton, $plugin->plugins[$i])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sets an error message.
     *
     * @param string $msg Error message.
     *
     * @return void
     */
    public function setError($msg)
    {
        $this->isValid = false;
        $this->errorMessage = $msg;
    }

    /**
     * Clears the validation data.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     *
     * @return void
     */
    public function clearValidation(Zikula_Form_View $view)
    {
        $this->isValid = true;
        $this->errorMessage = null;
    }

    /**
     * Saves value in data object.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     * @param array            &$data Data object.
     *
     * @return void
     */
    public function saveValue(Zikula_Form_View $view, &$data)
    {
        if ($this->dataBased) {
            if ($this->group == null) {
                if ($this->checked) {
                    $data[$this->dataField] = $this->value;
                }
            } else {
                if ($this->checked) {
                    if (!array_key_exists($this->group, $data)) {
                        $data[$this->group] = [];
                    }
                    $data[$this->group][$this->dataField] = $this->value;
                }
            }
        }
    }
}
