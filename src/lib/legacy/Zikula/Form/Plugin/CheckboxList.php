<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Checkbox list
 *
 * Renders a list of checkboxes with the supplied items.
 * Usefull for selecting multiple items.
 *
 * You can set the items directly like this:
 * <code>
 * {formcheckboxlist id='mylist' items=$items}
 * </code>
 * with the form event handler code like this:
 * <code>
 * class mymodule_user_testHandler extends Zikula_Form_Handler
 * {
 *     function initialize(Zikula_Form_View $view)
 *     {
 *         $items = [
 *             ['text' => 'A', 'value' => '1'],
 *             ['text' => 'B', 'value' => '2'],
 *             ['text' => 'C', 'value' => '3']
 *         ];
 *
 *         $view->assign('items', $items); // Supply items
 *         $view->assign('mylist', 2);     // Supply selected value
 *     }
 * }
 * </code>
 * Or you can set them indirectly using the plugin's databased features:
 * <code>
 * {formcheckboxlist id='mylist'}
 * </code>
 * with the form event handler code like this:
 * <code>
 * class mymodule_user_testHandler extends Zikula_Form_Handler
 * {
 *     function initialize(Zikula_Form_View $view)
 *     {
 *         $items = [
 *             ['text' => 'A', 'value' => '1'],
 *             ['text' => 'B', 'value' => '2'],
 *             ['text' => 'C', 'value' => '3']
 *         ];
 *
 *         $view->assign('mylistItems', $items);  // Supply items
 *         $view->assign('mylist', 2);            // Supply selected value
 *     }
 * }
 * </code>
 *
 * The resulting dataset is a list of strings representing the selected
 * values. So when you do a $data = $view->getValues(); you will
 * get a dataset like this:
 *
 * <code>
 * [
 *     'xxx' => 'valueXX',
 *     'checkboxes' => ['15','17','22','34'],
 *     'yyy' => 'valueYYY'
 * ]
 * </code>
 *
 * @deprecated for Symfony2 Forms
 */
class Zikula_Form_Plugin_CheckboxList extends Zikula_Form_Plugin_BaseListSelector
{
    /**
     * Selected value(s).
     *
     * The selected value(s) of a checkboxlist is an array of the item values.
     * You can assign to this in your templates like:
     * <code>
     * {formcheckboxlist selectedValue=B}
     * </code>
     * But in your code you should use {@link Zikula_Form_Plugin_CheckboxList::setSelectedValue()}
     * and {@link Zikula_Form_Plugin_CheckboxList::getSelectedValue()}.
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
    public function getFilename()
    {
        return __FILE__;
    }

    /**
     * Create event handler.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     * @param array            &$params Parameters passed from the Smarty plugin function.
     *
     * @see    Zikula_Form_AbstractPlugin
     * @return void
     */
    public function create(Zikula_Form_View $view, &$params)
    {
        parent::create($view, $params);
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
        parent::load($view, $params);

        if (array_key_exists('selectedValue', $params)) {
            $this->setSelectedValue($params['selectedValue']);
        }
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
        $readOnlyHtml = ($this->readOnly ? " disabled=\"disabled\"" : '');

        $class = 'z-form-checkboxlist';
        if ($this->readOnly) {
            $class .= ' z-form-readonly';
        }
        if ($this->cssClass != null) {
            $class .= ' ' . $this->cssClass;
        }

        $classHtml = ($class == '' ? '' : " class=\"$class\"");
        $nameHtml = " name=\"{$this->inputName}[]\"";

        $selectedByValue = [];
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
            $result .= "<div class=\"z-formlist\"{$style}>";
            $result .= "<input{$idHtml}{$nameHtml} type=\"checkbox\" value=\"$value\"{$selected}{$readOnlyHtml}{$classHtml} /> ";
            $result .= "<label for=\"{$this->id}_{$i}\">{$text}</label>\n";
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
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     *
     * @return void
     */
    public function decode(Zikula_Form_View $view)
    {
        // Do not read new value if readonly (evil submiter might have forged it)
        // Besides that, a disabled checkbox returns nothing at all, so old values are good to keep
        if (!$this->readOnly) {
            $value = $this->request->request->get($this->inputName, null);
            if ($value == null) {
                $value = [];
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
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     *
     * @return void
     */
    public function validate(Zikula_Form_View $view)
    {
        $this->clearValidation($view);

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
     * Called by the render when doing $view->getValues()
     * Uses the group parameter to decide where to store data.
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
                $data[$this->dataField] = $this->getSelectedValue();
            } else {
                if (!array_key_exists($this->group, $data)) {
                    $data[$this->group] = [];
                }
                $data[$this->group][$this->dataField] = $this->getSelectedValue();
            }
        }
    }

    /**
     * Load values.
     *
     * Called internally by the plugin itself to load values from the render.
     * Can also by called when some one is calling the render object's Zikula_Form_ViewetValues.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     * @param array            &$values Values to load.
     *
     * @return void
     */
    public function loadValue(Zikula_Form_View $view, &$values)
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
    public function setSelectedValue($value)
    {
        if (is_string($value)) {
            $value = explode(':', $value);
        } elseif (!is_array($value)) {
            $value = [$value];
        }

        $this->selectedValue = $value;
    }

    /**
     * Get the selected value.
     *
     * @return mixed The selected value.
     */
    public function getSelectedValue()
    {
        if ($this->saveAsString) {
            $s = '';
            for ($i = 0, $count = count($this->selectedValue); $i < $count; ++$i) {
                $s .= (empty($s) ? '' : ':') . $this->selectedValue[$i];
            }

            return ":{$s}:";
        }

        return $this->selectedValue;
    }
}
