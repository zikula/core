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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;

class ExportUsersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', CheckboxType::class, [
                'label' => 'Export title row',
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false,
                'data' => true
            ])
            ->add('email', CheckboxType::class, [
                'label' => 'Export email address',
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false,
                'data' => true
            ])
            ->add('user_regdate', CheckboxType::class, [
                'label' => 'Export registration date',
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false,
                'data' => true
            ])
            ->add('lastlogin', CheckboxType::class, [
                'label' => 'Export last login date',
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false,
                'data' => true
            ])
            ->add('groups', CheckboxType::class, [
                'label' => 'Export group memberships',
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false
            ])
            ->add('filename', TextType::class, [
                'label' => 'CSV filename',
                'help' => 'A simple name with three letter suffix, e.g. `myfile.csv`',
                'data' => 'user.csv',
                'constraints' => [
                    new Regex(['pattern' => '/^[\w,\s-]+\.[A-Za-z]{3}$/'])
                ]
            ])
            ->add('delimiter', ChoiceType::class, [
                'label' => 'CSV delimiter',
                'choices' => [
                    ',' => ',',
                    ';' => ';',
                    ':' => ':',
                    'tab' => '\t'
                ]
            ])
            ->add('download', SubmitType::class, [
                'label' => 'Download',
                'icon' => 'fa-download',
                'attr' => ['class' => 'btn btn-success']
            ])
            ->add('cancel', SubmitType::class, [
                'label' => 'Cancel',
                'icon' => 'fa-times',
                'attr' => ['class' => 'btn btn-default']
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulausersmodule_exportusers';
    }
}
