<?php

class ExampleDoctrine_Form_UserMetadataType extends \Symfony\Component\Form\AbstractType
{
    public function buildForm(Symfony\Component\Form\FormBuilder $builder, array $options)
    {
        $builder->add('comment')
                ->add('publisher');
    }

    public function getName()
    {
        return 'metadata';
    }
    
    public function getDefaultOptions(array $options) {
        return array('data_class' => 'ExampleDoctrine_Entity_UserMetadata',
                     'empty_data' => function($form){ 
                                        return new ExampleDoctrine_Entity_UserMetadata($form->getParent()->getData()); 
                                    });
    }
}
