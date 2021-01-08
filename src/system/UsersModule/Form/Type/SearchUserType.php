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

namespace Zikula\UsersModule\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Translation\Extractor\Annotation\Ignore;
use Translation\Extractor\Annotation\Translate;
use Zikula\GroupsModule\Entity\GroupEntity;
use Zikula\UsersModule\Constant;

class SearchUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('uname', TextType::class, [
                'label' => 'User name',
                'required' => false,
                'input_group' => ['left' => '%', 'right' => '%']
            ])
            ->add('email', TextType::class, [
                'label' => 'Email address',
                'required' => false,
                'input_group' => ['left' => '%', 'right' => '%']
            ])
            ->add('activated', ChoiceType::class, [
                'label' => 'User status',
                'required' => false,
                'choices' => [
                    'pending' => Constant::ACTIVATED_PENDING_REG,
                    'active' => Constant::ACTIVATED_ACTIVE,
                    'inactive' => Constant::ACTIVATED_INACTIVE,
                    'marked for deletion' => Constant::ACTIVATED_PENDING_DELETE
                ]
            ])
            ->add('groups', EntityType::class, [
                'class' => GroupEntity::class,
                'choice_label' => 'name',
                'multiple' => true,
                'placeholder' => 'Any group',
                'label' => 'Group membership',
                'required' => false
            ])
            ->add('registered_after', DateType::class, [
                'required' => false,
                'format' => 'yyyy-MM-dd',
                /** @Ignore */
                'placeholder' => [
                    'year' => /** @Translate */ 'Year',
                    'month' => /** @Translate */ 'Month',
                    'day' => /** @Translate */ 'Day'
                ]
            ])
            ->add('registered_before', DateType::class, [
                'label' => 'Registration date before',
                'required' => false,
                'format' => 'yyyy-MM-dd',
                /** @Ignore */
                'placeholder' => [
                    'year' => /** @Translate */ 'Year',
                    'month' => /** @Translate */ 'Month',
                    'day' => /** @Translate */ 'Day'
                ]
            ])
            ->add('search', SubmitType::class, [
                'label' => 'Search',
                'icon' => 'fa-search',
                'attr' => [
                    'class' => 'btn-success'
                ]
            ])
            ->add('cancel', SubmitType::class, [
                'label' => 'Cancel',
                'icon' => 'fa-times'
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulausersmodule_searchuser';
    }
}
