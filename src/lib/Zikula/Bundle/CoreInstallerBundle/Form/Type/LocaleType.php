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

class LocaleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('locale', 'choice', array(
                'label' => __('Select your default language'),
                'label_attr' => array('class' => 'col-lg-3'),
                'choices' => \ZLanguage::getInstalledLanguageNames(),
                'data' => \ZLanguage::getLanguageCode()))
            ->add('save', 'submit', array('label' => __('Next')));
    }

    public function getName()
    {
        return 'locale';
    }
}