<?php

namespace SystemPlugin\Symfony2Forms\Validation\Builder\Impl;

use SystemPlugin\Symfony2Forms\Validation\Builder\FieldValidatorBuilder;
use SystemPlugin\Symfony2Forms\Validation\Builder\ValidatorBuilder;
use SystemPlugin\Symfony2Forms\Validation\Constraints;
use SystemPlugin\Symfony2Forms\Validation\Constraint;

/**
 *
 */
class FieldValidatorBuilderImpl implements FieldValidatorBuilder
{
    private $field;
    
    /**
     * @var ValidatorBuilder
     */
    private $parent;
    
    /**
     * @var \SystemPlugin\Symfony2Forms\Validation\Constraint[]
     */
    private $constraints;
    
    public function __construct($field, ValidatorBuilder $parent)
    {
        $this->field = $field;
        $this->parent = $parent;
        $this->constraints = array();
    }
    
    
    public function buildField()
    {
        $this->parent->addConstraints($this->field, $this->constraints);
        return $this->parent;
    }

    public function hasMaxLength($max, $charset='UTF-8')
    {
        $this->constraints[] = new Constraints\MaxLength($max, $charset);
        return $this;
    }

    public function hasMaxValue($maxValue)
    {
        $this->constraints[] = new Constraints\MaxValue($maxValue);
        return $this;
    }

    public function hasMinLength($min, $charset='UTF-8')
    {
        $this->constraints[] = new Constraints\MinLength($min, $charset);
        return $this;
    }

    public function hasMinValue($minValue)
    {
        $this->constraints[] = new Constraints\MinValue($minValue);
        return $this;
    }

    public function hasType($type)
    {
        $this->constraints[] = new Constraints\Type($type);
        return $this;
    }

    public function isDate()
    {
        $this->constraints[] = new Constraints\Date();
        return $this;
    }

    public function isDateTime()
    {
        $this->constraints[] = new Constraints\DateTime();
        return $this;
    }

    public function isEmail()
    {
        $this->constraints[] = new Constraints\Email();
        return $this;
    }

    public function isFalse()
    {
        $this->constraints[] = new Constraints\False();
        return $this;
    }

    public function isNotBlank()
    {
        $this->constraints[] = new Constraints\NotBlank();
        return $this;
    }

    public function isNotNull()
    {
        $this->constraints[] = new Constraints\NotNull();
        return $this;
    }

    public function isTime()
    {
        $this->constraints[] = new Constraints\Time();
        return $this;
    }

    public function isTrue()
    {
        $this->constraints[] = new Constraints\True();
        return $this;
    }

    public function isUrl($protocols = array('http', 'https'))
    {
        $this->constraints[] = new Constraints\Url($protocols);
        return $this;
    }

    public function isValidReference()
    {
        
    }

    public function isValidArray()
    {
        return new SubValidatorBuilderImpl(
                $this,
                SubValidatorBuilderImpl::MODE_ARRAY
        );
    }
    
    public function isValidObject()
    {
        return new SubValidatorBuilderImpl(
                $this,
                SubValidatorBuilderImpl::MODE_OBJECT
        );
    }

    public function matchesRegex($regex)
    {
        
    }

    public function choice(array $validValues)
    {
        
    }
    
    public function constraint(Constraint $constraint)
    {
        $this->constraints[] = $constraint;
    }
}

