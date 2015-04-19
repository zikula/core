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
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Callback;

class RequestContextType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('router:request_context:host', 'text', array(
                'label' => __('The host where you install Zikula, e.g. "example.com". Do not include subdirectories.'),
                'label_attr' => array('class' => 'col-sm-3'),
                'data' => __('localhost'),
                'constraints' => array(
                    new NotBlank(),
                )))
            ->add('router:request_context:scheme', 'choice', array(
                'label' => __('Please enter the scheme of where you install Zikula, can be either "http" or "https"'),
                'label_attr' => array('class' => 'col-sm-3'),
                'choices' => array(
                    'http' => 'http',
                    'https' => 'https'
                ),
                'data' => 'http',
                ))
            ->add('router:request_context:base_url', 'text', array(
                'label' => __('Please enter the url path of the directory where you install Zikula, leave empty if you install it at the top level. Example: /my/sub-dir'),
                'label_attr' => array('class' => 'col-sm-3'),
            ));
    }

    public function getName()
    {
        return 'router_request_context';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                'csrf_protection' => false,
//                'csrf_field_name' => '_token',
//                // a unique key to help generate the secret token
//                'intention'       => '_zk_bdcreds',
            )
        );
    }
}