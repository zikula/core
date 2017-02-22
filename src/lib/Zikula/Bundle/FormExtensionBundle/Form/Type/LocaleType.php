<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\FormExtensionBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Locale form type.
 */
class LocaleType extends AbstractType
{
    /**
* @inheritDoc
     */
    public function configureDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => ['English' => 'en'],
            'label' => __('Locale'),
            'required' => false,
            'placeholder' => __('All'),
            'choices_as_values' => true
        ]);
    }

    /**
* @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'zikula_locale';
    }

    /**
* @inheritDoc
     */
    public function getParent()
    {
        return 'Symfony\Component\Form\Extension\Core\Type\ChoiceType';
    }
}
