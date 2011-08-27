<?php

namespace Zikula\ModulesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ModuleType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('class')
            ->add('state')
        ;
    }

    public function getName()
    {
        return 'zikula_modulesbundle_moduletype';
    }
}
