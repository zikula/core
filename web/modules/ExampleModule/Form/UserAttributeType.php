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

use ExampleModule\Entity\UserAttribute;

class UserAttributeType extends \Symfony\Component\Form\AbstractType
{
    public function buildForm(\Symfony\Component\Form\FormBuilder $builder, array $options)
    {
        $builder->add('value')
            ->add('name', 'hidden', array('data' => $options['attribute_name']));
    }

    public function getName()
    {
        return 'attribute';
    }

    public function getDefaultOptions(array $options)
    {
        return array('data_class' => 'ExampleModule\Entity\UserAttribute',
            'attribute_name' => 'n/a',
            'empty_data' => function($form) use($options)
            {
                return new UserAttribute(null,
                                         null,
                                         $form->getParent()->getParent()->getData());
            });
    }
}
