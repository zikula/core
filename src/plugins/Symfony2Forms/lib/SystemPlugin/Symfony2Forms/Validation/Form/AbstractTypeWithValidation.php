<?php

namespace SystemPlugin\Symfony2Forms\Validation\Form;

use Symfony\Component\Form\AbstractType;
use SystemPlugin\Symfony2Forms\Validation\ValidationRegistry;

/**
 *
 */
abstract class AbstractTypeWithValidation extends AbstractType
{
    public function __construct()
    {
        $options = $this->getDefaultOptions(array());
        
        if(isset($options['data_class'])) {
            ValidationRegistry::registerClassValidator($options['data_class'], $this->getValidator());
        }
    }
    
    public abstract function getValidator();
}
