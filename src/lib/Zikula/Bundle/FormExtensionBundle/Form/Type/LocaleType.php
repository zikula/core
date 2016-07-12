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
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LocaleType extends AbstractType
{
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'choices' => \ZLanguage::getInstalledLanguageNames(),
            'label' => __('Locale'),
            'required' => false,
            'placeholder' => __('All')
        ]);
    }

    public function getName()
    {
        return 'zikula_locale';
    }

    public function getParent()
    {
        return 'choice';
    }
}
