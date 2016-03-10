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
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocaleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('locale', 'choice', array(
                'label' => __('Select your default language'),
                'label_attr' => array('class' => 'col-sm-3'),
                'choices' => $options['choices'],
                'data' => $options['choice']
            ));
    }

    public function getName()
    {
        return 'locale';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
            'choices' => \ZLanguage::getInstalledLanguageNames(),
            'choice' => \ZLanguage::getLanguageCode()
//                'csrf_field_name' => '_token',
//                // a unique key to help generate the secret token
//                'intention'       => '_zk_bdcreds',
        ));
    }
}
