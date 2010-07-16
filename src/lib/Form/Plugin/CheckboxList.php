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
 * Checkbox list
 *
 * Renders a list of checkboxes with the supplied items.
 * Usefull for selecting multiple items.
 *
 * You can set the items directly like this:
 * <code>
 * <!--[formcheckboxlist id="mylist" items=$items]-->
 * </code>
 * with the form event handler code like this:
 * <code>
 * class mymodule_user_testHandler extends pnFormHandler
 * {
 * function initialize(&$render)
 * {
 * $items = array( array('text' => 'A', 'value' => '1'),
 * array('text' => 'B', 'value' => '2'),
 * array('text' => 'C', 'value' => '3') );
 *
 * $render->assign('items', $items); // Supply items
 * $render->assign('mylist', 2);     // Supply selected value
 * }
 * }
 * </code>
 * Or you can set them indirectly using the plugin's databased features:
 * <code>
 * <!--[formcheckboxlist id="mylist"]-->
 * </code>
 * with the form event handler code like this:
 * <code>
 * class mymodule_user_testHandler extends pnFormHandler
 * {
 * function initialize(&$render)
 * {
 * $items = array( array('text' => 'A', 'value' => '1'),
 * array('text' => 'B', 'value' => '2'),
 * array('text' => 'C', 'value' => '3') );
 *
 * $render->assign('mylistItems', $items);  // Supply items
 * $render->assign('mylist', 2);            // Supply selected value
 * }
 * }
 * </code>
 *
 * The resulting dataset is a list of strings representing the selected
 * values. So when you do a $data = $render->getValues(); you will
 * get a dataset like this:
 *
 * <code>
 * array('xxx' => 'valueXX',
 * 'checkboxes' => array('15','17','22','34'),
 * 'yyy' => 'valueYYY')
 * </code>
 */
class Form_Plugin_CheckboxList extends Form_Plugin_BaseListSelector
{
    /**
     * Selected value(s).
     *
     * The selected value(s) of a checkboxlist is an array of the item values.
     * You can assign to this in your templates like:
     * <code>
     * <!--[formcheckboxlist selectedValue=B]-->
     * </code>
     * But in your code you should use {@link pnFormCheckboxList::setSelectedValue()}
     * and {@link pnFormCheckboxList::getSelectedValue()}.
     *
     * @var array
     */
    public $selectedValue;

    /**
     * HTML input name for this plugin. Defaults to the ID of the plugin.
     *
     * @var string
     */
    public $inputName;

    /**
     * Number of columns to display checkboxes in.
     *
     * @var integer
     */
    public $repeatColumns;

    /**
     * Width of each checkbox list item (combination of checkbox and label).
     *
     * @var string Width including CSS unit (for instance "200px").
     */
    public $repeatWidth;

    /**
     * Enable saving of selected values as a colon delimited string.
     *
     * Enable this to save the selected values as a single string instead of
     * an array of selected values. The result is a colon separated string
     * like ":10:20:30".
     *
     * @var boolean
     */
    public $saveAsString;

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
     * @param Form_View &$render Reference to Form render object.
     * @param array       $params  Parameters passed from the Smarty plugin function.
     *
     * @see    Form_Plugin
     * @return void
     */
    function create(&$render, $params)
    {
        parent::create($render, $params);
    }

    /**
     * Load event handler.
     *
     * @param Form_View &$render Reference to pnForm render object.
     * @param array       &$params Parameters passed from the Smarty plugin function.
     *
     * @return void
     */
    function load(&$render, &$params)
    {
        parent::load($render, $params);

        if (array_key_exists('selectedValue', $params)) {
            $this->setSelectedValue($params['selectedValue']);
        }
    }

    /**
     * Render event handler.
     *
     * @param Form_View &$render Reference to Form render object.
     *
     * @return string The rendered output
     */
    function render(&$render)
    {
        $readOnlyHtml = ($this->readOnly ? " disabled=\"disabled\"" : '');

        $class = '';
        if ($this->readOnly) {
            $class .= ' readonly';
        }
        if ($this->cssClass != null) {
            $class .= ' ' . $this->cssClass;
        }

        $classHtml = ($class == '' ? '' : " class=\"$class\"");
        $nameHtml = " name=\"{$this->inputName}[]\"";

        $selectedByValue = array();
        if (is_array($this->selectedValue)) {
            foreach ($this->selectedValue as $v) {
                $selectedByValue[$v] = 1;
            }
        }

        $result = '<div class="checkboxlist">';
        if ($this->repeatColumns > 0) {
            $result .= '<table>';
        }

        for ($i = 0, $count = count($this->items); $i < $count; ++$i) {
            if ($this->repeatColumns > 0 && ($i % $this->repeatColumns) == 0) {
                $result .= '<tr>';
            }

            $item = &$this->items[$i];
            $idHtml = " id=\"{$this->id}_$i\"";

            $text = DataUtil::formatForDisplay($item['text']);

            if ($item['value'] === null) {
                $value = '#null#';
            } else {
                $value = DataUtil::formatForDisplay($item['value']);
            }

            if (isset($selectedByValue[$value]) && $selectedByValue[$value]) {
                $selected = ' checked="checked"';
            } else {
                $selected = '';
            }

            if ($this->repeatColumns > 0) {
                $result .= '<td>';
            }

            if (!empty($this->repeatWidth)) {
                $style = " style=\"width: $this->repeatWidth\"";
            } else {
                $style = '';
            }
            $result .= "<div class=\"z-formlist\"$style>";
            $result .= "<input type=\"checkbox\" value=\"$value\"{$selected}{$idHtml}{$nameHtml}{$readOnlyHtml}{$classHtml}/> ";
            $result .= "<label for=\"{$this->id}_$i\">$text</label>\n";
            $result .= '</div>';

            if ($this->repeatColumns > 0) {
                $result .= '</td>';
            }

            if ($this->repeatColumns > 0 && ($i % $this->repeatColumns) == $this->repeatColumns - 1) {
                $result .= '</tr>';
            }
        }

        if ($this->repeatColumns > 0 && $i % $this->repeatColumns != 0) {
            $result .= '</tr>';
        }

        if ($this->repeatColumns > 0) {
            $result .= '</table>';
        }

        $result .= '</div>';

        return $result;
    }

    /**
     * Decode event handler.
     *
     * @param Form_View &$render Reference to Form render object.
     *
     * @return void
     */
    function decode(&$render)
    {
        // Do not read new value if readonly (evil submiter might have forged it)
        // Besides that, a disabled checkbox returns nothing at all, so old values are good to keep
        if (!$this->readOnly) {
            $value = FormUtil::getPassedValue($this->inputName, null, 'POST');
            if ($value == null) {
                $value = array();
            }
            for ($i = 0, $count = count($value); $i < $count; ++$i) {
                $value[$i] = ($value[$i] == '#null#' ? null : $value[$i]);
            }

            $this->setSelectedValue($value);
        }
    }

    /**
     * Validates the input.
     *
     * @param Form_View &$render Reference to Form render object.
     *
     * @return void
     */
    function validate(&$render)
    {
        $this->clearValidation($render);

        if ($this->mandatory && count($this->selectedValue) == 0) {
            $this->setError(__('Error! You must make a selection.'));
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
    }

    /**
     * Clears the validation data.
     *
     * @param Form_View &$render Reference to Form render object.
     *
     * @return void
     */
    function clearValidation(&$render)
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
     * @param Form_View &$render Reference to Form render object.
     * @param array       &$data   Data object.
     *
     * @return void
     */
    function saveValue(&$render, &$data)
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
     * Can also by called when some one is calling the render object's pnFormSetValues.
     *
     * @param Form_View &$render Reference to pnForm render object.
     * @param array       &$values Values to load.
     *
     * @return void
     */
    function loadValue(&$render, &$values)
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
            } else {
                if (isset($values[$this->group])) {
                    $data = $values[$this->group];
                    if (isset($data[$this->dataField])) {
                        $value = $data[$this->dataField];
                        if ($this->itemsDataField != null && isset($data[$this->itemsDataField])) {
                            $items = $data[$this->itemsDataField];
                        }
                    }
                }
            }

            if ($items != null) {
                $this->setItems($items);
            }

            $this->setSelectedValue($value);
        }
    }

    /**
     * Set the selected value.
     *
     * @param mixed $value Selected value.
     *
     * @return void
     */
    function setSelectedValue($value)
    {
        if (is_string($value)) {
            $value = explode(':', $value);
        } else if (!is_array($value)) {
            $value = array($value);
        }
        $this->selectedValue = $value;
    }

    /**
     * Get the selected value.
     *
     * @return mixed The selected value.
     */
    function getSelectedValue()
    {

        if ($this->saveAsString) {
            $s = '';
            for ($i = 0, $count = count($this->selectedValue); $i < $count; ++$i) {
                $s .= (empty($s) ? '' : ':') . $this->selectedValue[$i];
            }
            return ':' . $s . ':';
        }

        return $this->selectedValue;
    }
}

