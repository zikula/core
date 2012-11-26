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
 * Floating value input
 *
 * Use for text inputs where you only want to accept floats. The value saved by
 * {@link Zikula_Form_View::GetValues()} is either null or a valid float.
 */
class Zikula_Form_Plugin_FloatInput extends Zikula_Form_Plugin_TextInput
{
    /**
     * Minimum value for validation.
     *
     * @var float
     */
    public $minValue;

    /**
     * Maximum value for validation.
     *
     * @var float
     */
    public $maxValue;

    /**
     * Number of decimal places to display.
     *
     * @var integer
     */
    public $precision;

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
        // Check if precision is provided, is a number, is an integer (even if its data type is a string), and is non-negative.
        if (isset($params['precision']) && is_numeric($params['precision']) && ((int)$params['precision'] == $params['precision'])
                && ($params['precision'] >= 0)) {
            // TODO - should we check if it is a non-negative integer separately so that we can throw or log an error or warning?
            $this->precision = (int)$params['precision'];
        } else {
            $this->precision = 2;
        }

        $this->maxLength = 30;
        $params['width'] = '6em';

        parent::create($view, $params);
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
        $this->text = DataUtil::formatNumber($this->text, $this->precision);

        return Zikula_Form_Plugin_TextInput::render($view);
    }

    /**
     * Helper method to determine css class.
     *
     * @see    Zikula_Form_Plugin_TextInput
     *
     * @return string the list of css classes to apply
     */
    protected function getStyleClass()
    {
        $class = parent::getStyleClass();

        return str_replace('z-form-text', 'z-form-float', $class);
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
        parent::validate($view);

        if (!$this->isValid) {
            return;
        }

        if ($this->text !== '') {
            $this->text = DataUtil::transformNumberInternal($this->text);
            if (!is_numeric($this->text)) {
                $this->setError(__('Error! Invalid number.'));
            }

            $i = $this->text;
            if ($this->minValue !== null && $i < $this->minValue || $this->maxValue !== null && $i > $this->maxValue) {
                if ($this->minValue !== null && $this->maxValue !== null) {
                    $this->setError(__f('Error! Range error. Value must be between %1$s and %2$s.',
                                        array($this->minValue, $this->maxValue)));
                } elseif ($this->minValue !== null) {
                    $this->setError(__f('Error! The value must be %s or more.', $this->minValue));
                } elseif ($this->maxValue !== null) {
                    $this->setError(__f('Error! The value must be %s or less.', $this->maxValue));
                }
            }
        }
    }

    /**
     * Parses a value.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     * @param string           $text Text.
     *
     * @return string Parsed Text.
     */
    public function parseValue(Zikula_Form_View $view, $text)
    {
        if ($text === '') {
            return null;
        }

        // process float value
        $text = floatval($text);

        return $text;
    }

    /**
     * Format the value to specific format.
     *
     * @param Zikula_Form_View $view  Reference to Zikula_Form_View object.
     * @param string           $value The value to format.
     *
     * @return string Formatted value.
     */
    public function formatValue(Zikula_Form_View $view, $value)
    {
        // done already in render() above, this relates to #587
        //return DataUtil::formatNumber($value, $this->precision);
        return parent::formatValue($view, $value);
    }
}
