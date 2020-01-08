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

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\GroupsModule\Entity\GroupEntity;

class SearchUserType extends AbstractType
{
    use TranslatorTrait;

    public function __construct(TranslatorInterface $translator)
    {
        $this->setTranslator($translator);
    }

    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('uname', TextType::class, [
                'label' => $this->trans('User name'),
                'required' => false,
                'input_group' => ['left' => '%', 'right' => '%']
            ])
            ->add('email', TextType::class, [
                'label' => $this->trans('Email address'),
                'required' => false,
                'input_group' => ['left' => '%', 'right' => '%']
            ])
            ->add('groups', EntityType::class, [
                'class' => GroupEntity::class,
                'choice_label' => 'name',
                'multiple' => true,
                'placeholder' => $this->trans('Any group'),
                'label' => $this->trans('Group membership'),
                'required' => false
            ])
            ->add('registered_after', DateType::class, [
                'required' => false,
                'format' => 'yyyy-MM-dd',
                'placeholder' => [
                    'year' => $this->trans('Year'),
                    'month' => $this->trans('Month'),
                    'day' => $this->trans('Day')
                ]
            ])
            ->add('registered_before', DateType::class, [
                'label' => $this->trans('Registration date before'),
                'required' => false,
                'format' => 'yyyy-MM-dd',
                'placeholder' => [
                    'year' => $this->trans('Year'),
                    'month' => $this->trans('Month'),
                    'day' => $this->trans('Day')
                ]
            ])
            ->add('search', SubmitType::class, [
                'label' => $this->trans('Search'),
                'icon' => 'fa-search',
                'attr' => ['class' => 'btn btn-success']
            ])
            ->add('cancel', SubmitType::class, [
                'label' => $this->trans('Cancel'),
                'icon' => 'fa-times',
                'attr' => ['class' => 'btn btn-default']
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulausersmodule_searchuser';
    }
}
