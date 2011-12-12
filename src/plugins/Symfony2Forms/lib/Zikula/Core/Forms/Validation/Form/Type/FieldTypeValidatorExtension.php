<?php

namespace Zikula\Core\Forms\Validation\Form\Type;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilder;
use SystemPlugin\Symfony2Forms\Validation\Form\FormValidator;


/**
 *
 */
class FieldTypeValidatorExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->addValidator(new FormValidator($builder->getTypes()));
    }
    
    public function getExtendedType()
    {
        return 'field';
    }
}
