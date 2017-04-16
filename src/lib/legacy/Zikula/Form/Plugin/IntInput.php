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
 * Integer input
 *
 * Use for text inputs where you only want to accept integers. The value saved by
 * {@link Zikula_Form_View::GetValues()} is either null or a valid integer valid.
 * @deprecated for Symfony2 Forms
 */
class Zikula_Form_Plugin_IntInput extends Zikula_Form_Plugin_TextInput
{
    /**
     * Minimum value for validation.
     *
     * @var integer
     */
    public $minValue;

    /**
     * Maximum value for validation.
     *
     * @var integer
     */
    public $maxValue;

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
        $this->maxLength = 20;
        $params['width'] = '6em';
        $params['textMode'] = 'number';

        if (isset($this->minValue)) {
            $this->attributes['min'] = $this->minValue;
        }
        if (isset($this->maxValue)) {
            $this->attributes['max'] = $this->maxValue;
        }

        parent::create($view, $params);

        $this->regexValidationPattern = '/^\\s*[+-]?\\s*?[0-9]+\\s*$/';
        $this->regexValidationMessage = __('Error! Invalid integer.');
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

        return str_replace('z-form-text', 'z-form-int', $class);
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
        parent::validate($view);

        if (!$this->isValid) {
            return;
        }

        if ($this->text !== '') {
            $i = (int)$this->text;
            if (null !== $this->minValue && $i < $this->minValue || null !== $this->maxValue && $i > $this->maxValue) {
                if (null !== $this->minValue && null !== $this->maxValue) {
                    $this->setError(__f('Error! Range error. Value must be between %1$s and %2$s.', [$this->minValue, $this->maxValue]));
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
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object
     * @param string           $text Text
     *
     * @return string Parsed Text
     */
    public function parseValue(Zikula_Form_View $view, $text)
    {
        if ($text === '') {
            return null;
        }

        return (int)$text;
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
        $nameHtml = " name=\"{$this->inputName}\"";
        $titleHtml = ($this->toolTip != null ? ' title="' . $view->translateForDisplay($this->toolTip) . '"' : '');
        $readOnlyHtml = ($this->readOnly ? ' readonly="readonly" tabindex="-1"' : '');
        $sizeHtml = ($this->size > 0 ? " size=\"{$this->size}\"" : '');
        $maxLengthHtml = ($this->maxLength > 0 ? " maxlength=\"{$this->maxLength}\"" : '');
        $text = DataUtil::formatForDisplay($this->text);
        $class = $this->getStyleClass();

        $attributes = $this->renderAttributes($view);

        $minValueHtml = (isset($this->minValue) ? " min=\"{$this->minValue}\"" : '');
        $maxValueHtml = (isset($this->maxValue) ? " max=\"{$this->maxValue}\"" : '');

        $result = "<input type=\"number\" {$minValueHtml}{$maxValueHtml}{$idHtml}{$nameHtml}{$titleHtml}{$sizeHtml}{$maxLengthHtml}{$readOnlyHtml} class=\"{$class}\" value=\"{$text}\"{$attributes} />";
        if ($this->mandatory && $this->mandatorysym) {
            $result .= '<span class="z-form-mandatory-flag">*</span>';
        }

        return $result;
    }
}
