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
 * Floating value input
 *
 * Use for text inputs where you only want to accept floats. The value saved by
 * {@link Zikula_Form_View::GetValues()} is either null or a valid float.
 *
 * @deprecated for Symfony2 Forms
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
        //$params['textMode'] = 'number';
        //$this->attributes['step'] = (1 / pow(10, $this->precision));

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
            if (null !== $this->minValue && $i < $this->minValue || null !== $this->maxValue && $i > $this->maxValue) {
                if (null !== $this->minValue && null !== $this->maxValue) {
                    $this->setError(__f('Error! Range error. Value must be between %1$s and %2$s.',
                                        [$this->minValue, $this->maxValue]));
                } elseif (null !== $this->minValue) {
                    $this->setError(__f('Error! The value must be %s or more.', $this->minValue));
                } elseif (null !== $this->maxValue) {
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
