<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license MIT
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace ExampleModule\Form;

class UserType extends \Symfony\Component\Form\AbstractType
{
    public function buildForm(\Symfony\Component\Form\FormBuilder $builder, array $options)
    {
        $builder->add('username')
                ->add('password')
                ->add('attributes', new UserAttributesType())
                ->add('metadata', new UserMetadataType())
                ->add('categories', 'categories', array('module' => 'ExampleModule',
                                                        'entity' => 'User', 
                                                        'entityCategoryClass' => 'ExampleModule\Entity\UserCategory'));
    }

    public function getName()
    {
        return 'user';
    }
    
    public function getDefaultOptions(array $options) {
        return array('data_class' => 'ExampleModule\Entity\User');
    }
}
