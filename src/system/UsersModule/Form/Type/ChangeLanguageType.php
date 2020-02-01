<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Zikula\Bundle\FormExtensionBundle\Form\Type\LocaleType;

class ChangeLanguageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('locale', LocaleType::class, [
                'label' => 'Choose language',
                'placeholder' => 'Site default',
                'required' => false
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Save',
                'icon' => 'fa-check',
                'attr' => ['class' => 'btn btn-success']
            ])
            ->add('cancel', SubmitType::class, [
                'label' => 'Cancel',
                'icon' => 'fa-times',
                'attr' => ['class' => 'btn btn-secondary']
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulausersmodule_changelanguage';
    }
}
