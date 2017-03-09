<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
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
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Regex;

class ExportUsersType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', CheckboxType::class, [
                'label' => $options['translator']->__('Export title row'),
                'required' => false,
                'data' => true
            ])
            ->add('email', CheckboxType::class, [
                'label' => $options['translator']->__('Export email address'),
                'required' => false,
                'data' => true
            ])
            ->add('user_regdate', CheckboxType::class, [
                'label' => $options['translator']->__('Export registration date'),
                'required' => false,
                'data' => true
            ])
            ->add('lastlogin', CheckboxType::class, [
                'label' => $options['translator']->__('Export last login date'),
                'required' => false,
                'data' => true
            ])
            ->add('groups', CheckboxType::class, [
                'required' => false,
                'label' => $options['translator']->__('Export group memberships'),
            ])
            ->add('filename', TextType::class, [
                'label' => $options['translator']->__('CSV filename'),
                'help' => $options['translator']->__('A simple name with three letter suffix, e.g. `myfile.csv`'),
                'data' => 'user.csv',
                'constraints' => [
                    new Regex(['pattern' => '/^[\w,\s-]+\.[A-Za-z]{3}$/'])
                ]
            ])
            ->add('delimiter', ChoiceType::class, [
                'label' => $options['translator']->__('CSV delimiter'),
                'choices' => [
                    ',' => ',',
                    ';' => ';',
                    ':' => ':',
                    'tab' => '\t'
                ]
            ])
            ->add('download', SubmitType::class, [
                'label' => $options['translator']->__('Download'),
                'icon' => 'fa-download',
                'attr' => ['class' => 'btn btn-success'],
            ])
            ->add('cancel', SubmitType::class, [
                'label' => $options['translator']->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => ['class' => 'btn btn-default']
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikulausersmodule_exportusers';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => null
        ]);
    }
}
