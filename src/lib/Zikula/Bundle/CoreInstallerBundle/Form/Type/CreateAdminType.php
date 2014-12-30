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
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Regex;

class CreateAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', 'text', array(
                'label' => __('Admin User Name'),
                'label_attr' => array('class' => 'col-lg-3'),
                'data' => __('admin'),
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('min' => 5)),
                    new Regex(array(
                        'pattern' => '/[^\p{L}\p{N}_\.\-]/u',
                        'message' => __('Error! Usernames can only consist of a combination of letters, numbers and may only contain the symbols . and _')
                    ))
                )))
            ->add('password', 'repeated', array(
                'type' => 'password',
                'invalid_message' => 'The password fields must match.',
                'options' => array(
                    'label_attr' => array('class' => 'col-lg-3'),
                    'constraints' => array(
                        new NotBlank(),
                        new Length(array('min' => 7, 'max' => 40))
                    )
                ),
                'required' => true,
                'first_options'  => array('label' => 'Admin Password'),
                'second_options' => array('label' => 'Repeat Password'),
                ))
            ->add('email', 'email', array(
                'label' => __('Admin Email Address'),
                'label_attr' => array('class' => 'col-lg-3'),
                'constraints' => array(
                    new NotBlank(),
                    new Email(), // this should take the place of the Regex below...
//                    new Regex(array(
//                        'pattern' => '/^(?:[^\s\000-\037\177\(\)<>@,;:\\"\[\]]\.?)+@(?:[^\s\000-\037\177\(\)<>@,;:\\\"\[\]]\.?)+\.[a-z]{2,6}$/Ui',
//                        'match' => false,
//                        'message' => __("Error! The administrator's e-mail address is not correctly formed. Please correct your entry and try again.")
//                    ))
                )))
            ->add('save', 'submit', array('label' => __('Next')));
    }

    public function getName()
    {
        return 'createadmin';
    }
}