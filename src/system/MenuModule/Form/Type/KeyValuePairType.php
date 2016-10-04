<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\MenuModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class KeyValuePairType
 *
 * @see \Zikula\MenuModule\Form\EventListener\KeyValueFixerListener
 * @see \Zikula\MenuModule\Form\DataTransformer\KeyValueTransformer
 */
class KeyValuePairType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('key', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', $options['key_options'])
            ->add('value', 'Symfony\Component\Form\Extension\Core\Type\TextType', $options['value_options'])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulamenumodule_keyvaluepair';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'key_options' => [],
            'value_options' => [],
        ]);
    }
}
