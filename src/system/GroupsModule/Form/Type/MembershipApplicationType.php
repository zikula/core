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

namespace Zikula\GroupsModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Membership application form type class.
 */
class MembershipApplicationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('group', HiddenType::class, [
                'property_path' => 'group.gid'
            ])
            ->add('user', HiddenType::class, [
                'property_path' => 'user.uid'
            ])
            ->add('status', HiddenType::class)
            ->add('application', TextareaType::class, [
                'label' => 'Comment to attach to your application',
                'required' => false
            ])
            ->add('apply', SubmitType::class, [
                'label' => 'Apply',
                'icon' => 'fa-check',
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
        return 'zikulagroupsmodule_membershipapplication';
    }
}
