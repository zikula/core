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
 * Drop down list
 *
 * Renders an HTML <select> element with the supplied items.
 *
 * You can set the items directly like this:
 * <code>
 * {formdropdownlist id='mylist' items=$items}
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
 * {formdropdownlist id='mylist'}
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
 * Selected index is zero based. Selected value is a string - and the PHP null
 * value is also a valid value.
 *
 * Option groups can be added by setting an 'optgroup' attribute on each item.
 * For instance:
 *
 * <code>
 * class mymodule_user_testHandler extends Zikula_Form_Handler
 * {
 *     function initialize(Zikula_Form_View $view)
 *     {
 *         $items = [
 *             ['text' => 'A', 'value' => '1', 'optgroup' => 'AAA'],
 *             ['text' => 'B', 'value' => '2', 'optgroup' => 'BBB'],
 *             ['text' => 'C', 'value' => '3', 'optgroup' => 'CCC']
 *         ];
 *
 *         $view->assign('mylistItems', $items);  // Supply items
 *         $view->assign('mylist', 2);            // Supply selected value
 *     }
 * }
 * </code>
 *
 * You can also encourage reuse of dropdown lists by inheriting from
 * the dropdown list into a specialized list a'la MyCategorySelector or
 * MyColorSelector, and then use this plugin where ever you want
 * a category or color selector. In this way you don't have to remember
 * to assign the items to the render every time you need such a selector.
 * In these plugins you must set the items in the load event handler.
 * See {@link Zikula_Form_Plugin_LanguageSelector} for a good example of how this
 * can be done.
 *
 * @deprecated for Symfony2 Forms
 */
class Zikula_Form_Plugin_DropdownList extends Zikula_Form_Plugin_BaseListSelector
{
    /**
     * Selected value.
     *
     * You can assign to this in your templates like:
     * <code>
     * {formdropdownlist selectedValue=B}
     * </code>
     * But in your code you should use {@link Zikula_Form_Plugin_DropdownList::setSelectedValue()}
     * and {@link Zikula_Form_Plugin_DropdownList::getSelectedValue()}.
     *
     * Selected value is an array of values if you have set selectionMode=multiple.
     *
     * @var mixed
     */
    public $selectedValue;

    /**
     * Selected item index.
     *
     * You can assign to this in your templates like:
     * <code>
     * {formdropdownlist selectedIndex=2}
     * </code>
     * But in your code you should use {@link Zikula_Form_Plugin_DropdownList::setSelectedIndex()}
     * and {@link Zikula_Form_Plugin_DropdownList::getSelectedIndex()}.
     *
     * Select index is not valid when selectionMode=multiple.
     *
     * @var integer Zero based index
     */
    public $selectedIndex;

    /**
     * Enable or disable auto postback.
     *
     * Auto postback means "generate a server side event when selected index changes".
     * If enabled then the event handler named in $onSelectedIndexChanged will be fired
     * in the main form event handler.
     *
     * @var boolean
     */
    public $autoPostBack;

    /**
     * Enable or disable mandatory asterisk.
     *
     * @var boolean
     */
    public $mandatorysym;

    /**
     * Selection mode.
     *
     * Sets selection mode to either single item selection (standard dropdown) or
     * multiple item selection.
     *
     * @var string Possible values are 'single' and 'multiple'
     */
    public $selectionMode = 'single';

    /**
     * Size of dropdown.
     *
     * This corresponds to the "size" attribute of the HTML <select> element.
     *
     * @var integer
     */
    public $size = null;

    /**
     * Enable saving of multiple selected values as a colon delimited string.
     *
     * Enable this to save the selected values as a single string instead of
     * an array of selected values. The result is a colon separated string
     * like ":10:20:30".
     *
     * @var boolean
     */
    public $saveAsString;

    /**
     * Name of selected index changed method.
     *
     * @var string Default is "handleSelectedIndexChanged"
     */
    public $onSelectedIndexChanged = 'handleSelectedIndexChanged';

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
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object
     * @param array            &$params Parameters passed from the Smarty plugin function
     *
     * @see    Zikula_Form_AbstractPlugin
     * @return void
     */
    public function create(Zikula_Form_View $view, &$params)
    {
        parent::create($view, $params);

        $this->selectedIndex = -1;
    }

    /**
     * Load event handler.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object
     * @param array            &$params Parameters passed from the Smarty plugin function
     *
     * @return void
     */
    public function load(Zikula_Form_View $view, &$params)
    {
        parent::load($view, $params);

        // If someone decided to set selected value from the template then try to "set it for real"
        // (meaning: set also selected Index) - after the items, potentially, have been loaded.
        if (array_key_exists('selectedValue', $params)) {
            $this->setSelectedValue($params['selectedValue']);
        }

        if (array_key_exists('selectedIndex', $params)) {
            $this->setSelectedIndex($params['selectedIndex']);
        }
    }

    /**
     * Render event handler.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object
     *
     * @return string The rendered output
     */
    public function render(Zikula_Form_View $view)
    {
        $idHtml = $this->getIdHtml();

        $nameHtml = " name=\"{$this->inputName}[]\"";

        $readOnlyHtml = ($this->readOnly ? " disabled=\"disabled\"" : '');

        $class = 'z-form-dropdownlist';
        if (!$this->isValid) {
            $class .= ' z-form-error';
        }
        if ($this->mandatorysym) {
            $class .= ' z-form-mandatory';
        }
        if ($this->readOnly) {
            $class .= ' z-form-readonly';
        }
        if ($this->cssClass != null) {
            $class .= ' ' . $this->cssClass;
        }

        $classHtml = ($class == '' ? '' : " class=\"{$class}\"");

        $sizeHtml = ($this->size == null ? '' : " size=\"{$this->size}\"");

        $postbackHtml = '';
        if ($this->autoPostBack) {
            $postbackHtml = " onchange=\"" . $view->getPostBackEventReference($this, '') . "\"";
        }

        $multipleHtml = '';
        if ($this->selectionMode == 'multiple') {
            $multipleHtml = " multiple=\"multiple\"";
        }

        $attributes = $this->renderAttributes($view);
        $requiredFlag = $this->mandatory ? ' required="required"' : '';

        $result = "<select{$idHtml}{$nameHtml}{$readOnlyHtml}{$classHtml}{$postbackHtml}{$multipleHtml}{$sizeHtml}{$requiredFlag}{$attributes}>\n";
        $currentOptGroup = null;
        foreach ($this->items as $item) {
            $optgroup = (isset($item['optgroup']) ? $item['optgroup'] : null);
            if ($optgroup != $currentOptGroup) {
                if ($currentOptGroup != null) {
                    $result .= "</optgroup>\n";
                }
                if ($optgroup != null) {
                    $result .= "<optgroup label=\"" . DataUtil::formatForDisplay($optgroup) . "\">\n";
                }
                $currentOptGroup = $optgroup;
            }

            $text = DataUtil::formatForDisplay($item['text']);

            if ($item['value'] === null) {
                $value = '#null#';
            } else {
                $value = DataUtil::formatForDisplay($item['value']);
            }

            if ($this->selectionMode == 'single' && $value == $this->selectedValue) {
                $selected = ' selected="selected"';
            } elseif ($this->selectionMode == 'multiple' && in_array($value, (array)$this->selectedValue)) {
                $selected = ' selected="selected"';
            } else {
                $selected = '';
            }
            $result .= "<option value=\"{$value}\"{$selected}>{$text}</option>\n";
        }
        if ($currentOptGroup != null) {
            $result .= "</optgroup>\n";
        }
        $result .= "</select>\n";
        if ($this->mandatorysym) {
            $result .= '<span class="z-form-mandatory-flag">*</span>';
        }

        return $result;
    }

    /**
     * Called by Zikula_Form_View framework due to the use of getPostBackEventReference() above.
     *
     * @param Zikula_Form_View $view          Reference to Zikula_Form_View object
     * @param string           $eventArgument The event argument
     *
     * @return void
     */
    public function raisePostBackEvent(Zikula_Form_View $view, $eventArgument)
    {
        $args = [
            'commandName' => null,
            'commandArgument' => null
        ];
        if (!empty($this->onSelectedIndexChanged)) {
            $view->raiseEvent($this->onSelectedIndexChanged, $args);
        }
    }

    /**
     * Decode event handler.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object
     *
     * @return void
     */
    public function decode(Zikula_Form_View $view)
    {
        // Do not read new value if readonly (evil submiter might have forged it)
        if (!$this->readOnly) {
            $value = $this->request->request->get($this->inputName, null);
            $value = (null == $value) ? [] : (array)$value;

            for ($i = 0, $count = count($value); $i < $count; ++$i) {
                if ($value[$i] == '#null#') {
                    $value[$i] = null;
                }
            }

            if ($this->selectionMode == 'single') {
                $value = $value[0];
            }

            $this->setSelectedValue($value);
        }
    }

    /**
     * Validates the input.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object
     *
     * @return void
     */
    public function validate(Zikula_Form_View $view)
    {
        $this->clearValidation($view);

        // we have to allow 0 as a value, see #986
        $valueNotSelected = ((empty($this->selectedValue) && !is_numeric($this->selectedValue)) || $this->selectedValue === null);
        if ($this->mandatory && $valueNotSelected) {
            $this->setError(__('Error! You must make a selection.'));
        }
    }

    /**
     * Set the selected value.
     *
     * @param mixed $value Selected value
     *
     * @return void
     */
    public function setSelectedValue($value)
    {
        if ($this->selectionMode == 'single') {
            // Check for exiting value in list (avoid tampering with post values)
            for ($i = 0, $count = count($this->items); $i < $count; ++$i) {
                $item = $this->items[$i];

                if ($item['value'] == $value) {
                    $this->selectedValue = $value;
                    $this->selectedIndex = $i;
                }
            }
        } else {
            if (is_string($value)) {
                $value = explode(':', $value);
            }

            $ok = true;
            for ($j = 0, $jcount = count($value); $j < $jcount; ++$j) {
                $ok2 = false;
                // Check for exiting value in list (avoid tampering with post values)
                for ($i = 0, $icount = count($this->items); $i < $icount; ++$i) {
                    $item = $this->items[$i];

                    if ($item['value'] == $value[$j]) {
                        $ok2 = true;
                        break;
                    }
                }
                $ok = $ok && $ok2;
            }

            if ($ok) {
                $this->selectedValue = $value;
                $this->selectedIndex = 0;
            }
        }
    }

    /**
     * Get the selected value.
     *
     * @return mixed The selected value
     */
    public function getSelectedValue()
    {
        if ($this->saveAsString) {
            $s = '';
            for ($i = 0, $count = count($this->selectedValue); $i < $count; ++$i) {
                $s .= (empty($s) ? '' : ':') . $this->selectedValue[$i];
            }

            return $s;
        }

        return $this->selectedValue;
    }

    /**
     * Set the selected item by index.
     *
     * @param int $index Selected index
     *
     * @return void
     */
    public function setSelectedIndex($index)
    {
        if ($index >= 0 && $index < count($this->items)) {
            $this->selectedValue = $this->items[$index]['value'];
            $this->selectedIndex = $index;
        }
    }

    /**
     * Get the selected index.
     *
     * @return integer The selected index
     */
    public function getSelectedIndex()
    {
        return $this->selectedIndex;
    }
}
