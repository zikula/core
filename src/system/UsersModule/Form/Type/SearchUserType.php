<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
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
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;

class SearchUserType extends AbstractType
{
    use TranslatorTrait;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->setTranslator($translator);
    }

    /**
     * @param TranslatorInterface $translator
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('uname', TextType::class, [
                'label' => $this->__('User name'),
                'required' => false,
                'input_group' => ['left' => '%', 'right' => '%']
            ])
            ->add('email', TextType::class, [
                'label' => $this->__('Email address'),
                'required' => false,
                'input_group' => ['left' => '%', 'right' => '%']
            ])
            ->add('groups', EntityType::class, [
                'class' => 'ZikulaGroupsModule:GroupEntity',
                'choice_label' => 'name',
                'multiple' => true,
                'placeholder' => $this->__('Any group'),
                'label' => $this->__('Group membership'),
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
                'label' => $this->__('Registration date before'),
                'required' => false,
                'format' => 'yyyy-MM-dd',
                'placeholder' => [
                    'year' => 'Year', 'month' => 'Month', 'day' => 'Day'
                ]
            ])
            ->add('search', SubmitType::class, [
                'label' => $this->__('Search'),
                'icon' => 'fa-search',
                'attr' => ['class' => 'btn btn-success']
            ])
            ->add('cancel', SubmitType::class, [
                'label' => $this->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => ['class' => 'btn btn-danger']
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikulausersmodule_searchuser';
    }
}
