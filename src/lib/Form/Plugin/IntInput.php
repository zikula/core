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
 * Integer input
 *
 * Use for text inputs where you only want to accept integers. The value saved by
 * {@link pnForm::pnFormGetValues()} is either null or a valid integer valid.
 */
class Form_Plugin_IntInput extends Form_Plugin_TextInput
{
    /**
     * Minimum value for validation
     * @var int
     */
    protected $minValue;
    
    /**
     * Maximum value for validation
     * @var int
     */
    protected $maxValue;
    
    function getFilename()
    {
        return __FILE__;
    }
    
    function create(&$render, &$params)
    {
        $this->maxLength = 20;
        $params['width'] = '6em';
        parent::create($render, $params);
        $this->regexValidationPattern = '/^\\s*[+-]?\\s*?[0-9]+\\s*$/';
        $this->regexValidationMessage = __('Error! Invalid integer.');
    }
    
    function validate(&$render)
    {
        parent::validate($render);
        if (!$this->isValid) {
            return;
        }
        
        if ($this->text != '') {
            $i = (int) $this->text;
            if ($this->minValue != null && $i < $this->minValue || $this->maxValue != null && $i > $this->maxValue) {
                if ($this->minValue != null && $this->maxValue != null) {
                    $this->setError(__f('Error! Range error. Value must be between %1$s and %2$s.', array(
                        $this->minValue, 
                        $this->maxValue)));
                } else if ($this->minValue != null) {
                    $this->setError(__f('Error! The value must be %s or more.', $this->minValue));
                } else if ($this->maxValue != null) {
                    $this->setError(__f('Error! The value must be %s or less.', $this->maxValue));
                }
            }
        }
    }
    
    function parseValue(&$render, $text)
    {
        if ($text == '') {
            return null;
        }
        return (int) $text;
    }
}
