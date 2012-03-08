<?php

class ExampleDoctrine_Form_UserAttributesType extends \Symfony\Component\Form\AbstractType
{
    public function buildForm(Symfony\Component\Form\FormBuilder $builder, array $options)
    {
        $builder->add('field1', new ExampleDoctrine_Form_UserAttributeType(), array('attribute_name' => 'field1', 'property_path' => '[field1]'));
        $builder->add('field2', new ExampleDoctrine_Form_UserAttributeType(), array('attribute_name' => 'field2', 'property_path' => '[field2]'));
    }

    public function getName()
    {
        return 'attributes';
    }
}
