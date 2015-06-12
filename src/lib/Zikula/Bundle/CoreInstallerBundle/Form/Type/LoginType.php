<?php
/**
 * Copyright Zikula Foundation 2014 - Zikula CoreInstaller bundle.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;

class LoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', 'text', array(
                'label' => __('User Name'),
                'label_attr' => array('class' => 'col-sm-3'),
                'data' => __('admin'),
                'constraints' => array(
                    new NotBlank(),
                )))
            ->add('password', 'password', array(
                'label' => __('Password'),
                'label_attr' => array('class' => 'col-sm-3'),
                'constraints' => array(
                    new NotBlank(),
                )));
    }

    public function getName()
    {
        return 'login';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'constraints' => new Callback(array('callback' => array('Zikula\Bundle\CoreInstallerBundle\Validator\CoreInstallerValidator', 'validateAndLogin'))),
            'csrf_protection' => false,
//                'csrf_field_name' => '_token',
//                // a unique key to help generate the secret token
//                'intention'       => '_zk_bdcreds',
        ));
    }
}