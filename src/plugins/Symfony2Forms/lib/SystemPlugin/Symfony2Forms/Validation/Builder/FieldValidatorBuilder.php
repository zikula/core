<?php

namespace SystemPlugin\Symfony2Forms\Validation\Builder;

use SystemPlugin\Symfony2Forms\Validation\Constraint;

/**
 *
 */
interface FieldValidatorBuilder
{
    /**
     * @return ValidatorBuilder
     */
    public function buildField();
    
    /**
     * @return FieldValidatorBuilder
     */
    public function isFalse();
    
    /**
     * @return FieldValidatorBuilder
     */
    public function isTrue();
    
    /**
     * @return FieldValidatorBuilder
     */
    public function isUrl();
    
    /**
     * @return FieldValidatorBuilder
     */
    public function isTime();
    
    /**
     * @return FieldValidatorBuilder
     */
    public function matchesRegex($regex);
    
    /**
     * @return FieldValidatorBuilder
     */
    public function isNotNull();
    
    /**
     * @return FieldValidatorBuilder
     */
    public function isNotBlank();
    
    /**
     * @return FieldValidatorBuilder
     */
    public function hasMinLength($min);
    
    /**
     * @return FieldValidatorBuilder
     */
    public function hasMinValue($minValue);
    
    /**
     * @return FieldValidatorBuilder
     */
    public function hasMaxLength($max);
    
    /**
     * @return FieldValidatorBuilder
     */
    public function hasMaxValue($maxValue);
    
    /**
     * @return FieldValidatorBuilder
     */
    public function isEmail();
    
    /**
     * @return FieldValidatorBuilder
     */
    public function isDateTime();
    
    /**
     * @return FieldValidatorBuilder
     */
    public function isDate();
    
    /**
     * @return FieldValidatorBuilder
     */
    public function hasType($type);
    
    /**
     * @return FieldValidatorBuilder
     */
    public function isValidReference();
    
    /**
     * @return SubValidatorBuilder
     */
    public function isValidArray();
    
    /**
     * @return SubValidatorBuilder
     */
    public function isValidObject();
    
    /**
     * @return FieldValidatorBuilder
     */
    public function choice(array $validValues);
    
     /**
     * @return FieldValidatorBuilder
     */
    public function constraint(Constraint $constraint);
}

