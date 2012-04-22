<?php

class ExampleDoctrine_Form_UserAttributeType extends \Symfony\Component\Form\AbstractType
{
    public function buildForm(Symfony\Component\Form\FormBuilder $builder, array $options)
    {
        $builder->add('value')
                ->add('name', 'hidden', array('data' => $options['attribute_name']));
    }

    public function getName()
    {
        return 'attribute';
    }
    
    public function getDefaultOptions(array $options) {
        return array('data_class' => 'ExampleDoctrine_Entity_UserAttribute',
                     'attribute_name' => 'n/a',
                     'empty_data' => function($form) use($options) {
                                        return new ExampleDoctrine_Entity_UserAttribute(null,
                                                                                        null,
                                                                                        $form->getParent()->getParent()->getData());
                                    });
    }
}
