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

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('uname', TextType::class, [
                'label' => $options['translator']->__('User name'),
                'required' => false,
                'input_group' => ['left' => '%', 'right' => '%']
            ])
            ->add('email', TextType::class, [
                'label' => $options['translator']->__('Email address'),
                'required' => false,
                'input_group' => ['left' => '%', 'right' => '%']
            ])
            ->add('groups', EntityType::class, [
                'class' => 'ZikulaGroupsModule:GroupEntity',
                'choice_label' => 'name',
                'multiple' => true,
                'placeholder' => $options['translator']->__('Any group'),
                'label' => $options['translator']->__('Group membership'),
                'required' => false
            ])
            ->add('registered_after', DateType::class, [
                'required' => false,
                'format' => 'yyyy-MM-dd',
                'placeholder' => [
                    'year' => 'Year', 'month' => 'Month', 'day' => 'Day'
                ]
            ])
            ->add('registered_before', DateType::class, [
                'label' => $options['translator']->__('Registration date before'),
                'required' => false,
                'format' => 'yyyy-MM-dd',
                'placeholder' => [
                    'year' => 'Year', 'month' => 'Month', 'day' => 'Day'
                ]
            ])
            ->add('search', SubmitType::class, [
                'label' => $options['translator']->__('Search'),
                'icon' => 'fa-search',
                'attr' => ['class' => 'btn btn-success'],
            ])
            ->add('cancel', SubmitType::class, [
                'label' => $options['translator']->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => ['class' => 'btn btn-danger'],
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulausersmodule_searchuser';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => null,
        ]);
    }
}
