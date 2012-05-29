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

use ExampleModule\Entity\UserMetadata;

class UserMetadataType extends \Symfony\Component\Form\AbstractType
{
    public function buildForm(\Symfony\Component\Form\FormBuilder $builder, array $options)
    {
        $builder->add('comment')
                ->add('publisher');
    }

    public function getName()
    {
        return 'metadata';
    }
    
    public function getDefaultOptions(array $options) {
        return array('data_class' => 'ExampleModule\Entity\UserMetadata',
                     'empty_data' => function($form){ 
                                        return new UserMetadata($form->getParent()->getData());
                                    });
    }
}
