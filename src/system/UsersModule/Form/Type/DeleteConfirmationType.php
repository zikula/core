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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class DeleteConfirmationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('users', HiddenType::class)
            ->add('force', ChoiceType::class, [
                'label' => 'Deletion type',
                'choices' => [
                    'Ghost (soft delete, recommended)' => false,
                    'Full delete' => true
                ],
                'data' => false,
                'expanded' => true,
                'multiple' => false
            ])
            ->add('delete', SubmitType::class, [
                'label' => 'Confirm deletion',
                'icon' => 'fa-trash-alt',
                'attr' => [
                    'class' => 'btn-danger'
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
        return 'zikulausersmodule_deleteconfirmation';
    }
}
