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

namespace Zikula\ProfileModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class UsersBlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('ublockon', CheckboxType::class, [
                'label'    => 'Enable your personal custom block',
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false,
            ])
            ->add('ublock', TextareaType::class, [
                'label'    => 'Content of your custom block',
                'required' => false,
                'attr'     => [
                    'cols' => 80,
                    'rows' => 10,
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save',
                'icon'  => 'fa-check',
                'attr'  => [
                    'class' => 'btn-success'
                ]
            ])
            ->add('cancel', SubmitType::class, [
                'label' => 'Cancel',
                'icon'  => 'fa-times',
                'attr'  => [
                    'formnovalidate' => 'formnovalidate'
                ]
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulaprofilemodule_usersblock';
    }
}
