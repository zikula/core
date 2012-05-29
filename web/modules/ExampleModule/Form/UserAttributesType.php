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

class UserAttributesType extends \Symfony\Component\Form\AbstractType
{
    public function buildForm(\Symfony\Component\Form\FormBuilder $builder, array $options)
    {
        $builder->add('field1', new UserAttributeType(), array('attribute_name' => 'field1', 'property_path' => '[field1]'));
        $builder->add('field2', new UserAttributeType(), array('attribute_name' => 'field2', 'property_path' => '[field2]'));
    }

    public function getName()
    {
        return 'attributes';
    }
}
