<?php

class ExampleDoctrine_Form_ContactType extends SystemPlugin\Symfony2Forms\Validation\Form\AbstractTypeWithValidation
{
    public function buildForm(Symfony\Component\Form\FormBuilder $builder, array $options)
    {
        $builder->add('username')
                ->add('password')
                ->add('metadata', new ExampleDoctrine_Form_MetadataType())
                ->add('categories', 'categories', array('module' => 'ExampleDoctrine', 
                                                        'entity' => 'User', 
                                                        'entityCategoryClass' => 'ExampleDoctrine_Entity_UserCategory'));
    }

    public function getName()
    {
        return 'contact';
    }
    
    public function getValidator() {
        return \SystemPlugin\Symfony2Forms\Validation\Builder\ValidatorBuilderFactory::create()
                    ->forField('username')->isNotBlank()->buildField()
                    ->forField('password')->isNotBlank()->buildField()
                    ->forField('metadata')->isNotBlank()->buildField()
                ->buildValidator();
    }
    
    public function getDefaultOptions(array $options) {
        return array('data_class' => 'ExampleDoctrine_Entity_User');
    }
}
