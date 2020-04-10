<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\FormExtensionBundle\Form\Type\DynamicOptions;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class FormOptionsArrayType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('required', CheckboxType::class, [
                'label' => 'Required',
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false
            ])
            ->add('help', TextType::class, [
                'label' => 'Help text',
                'required' => false
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulaformextensionbundle_formoptionsarray';
    }
}
