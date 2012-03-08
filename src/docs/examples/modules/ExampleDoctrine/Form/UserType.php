<?php

class ExampleDoctrine_Form_UserType extends \Symfony\Component\Form\AbstractType
{
    public function buildForm(Symfony\Component\Form\FormBuilder $builder, array $options)
    {
        $builder->add('username')
                ->add('password')
                ->add('attributes', new ExampleDoctrine_Form_UserAttributesType())
                ->add('metadata', new ExampleDoctrine_Form_UserMetadataType())
                ->add('categories', 'categories', array('module' => 'ExampleDoctrine', 
                                                        'entity' => 'User', 
                                                        'entityCategoryClass' => 'ExampleDoctrine_Entity_UserCategory'));
    }

    public function getName()
    {
        return 'user';
    }
    
    public function getDefaultOptions(array $options) {
        return array('data_class' => 'ExampleDoctrine_Entity_User');
    }
}
