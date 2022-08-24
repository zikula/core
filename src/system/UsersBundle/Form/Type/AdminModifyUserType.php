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

namespace Zikula\UsersBundle\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Zikula\GroupsBundle\Entity\GroupEntity;
use Zikula\UsersBundle\UsersConstant;

class AdminModifyUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('activated', ChoiceType::class, [
                'choices' => [
                    'Active' => UsersConstant::ACTIVATED_ACTIVE,
                    'Inactive' => UsersConstant::ACTIVATED_INACTIVE,
                    'Pending' => UsersConstant::ACTIVATED_PENDING_REG,
                ],
                'label' => 'User status',
            ])
            ->add('groups', EntityType::class, [
                'class' => GroupEntity::class,
                'choice_label' => 'name',
                'expanded' => true,
                'multiple' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Save',
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn-success',
                ],
            ])
            ->add('cancel', SubmitType::class, [
                'label' => 'Cancel',
                'icon' => 'fa-times',
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulausersbundle_adminmodifyuser';
    }
}
