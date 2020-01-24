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

namespace Zikula\Bundle\FormExtensionBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * General deletion form type.
 */
class DeletionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('delete', SubmitType::class, [
                'label' => 'Delete',
                'icon' => 'fa-trash-alt',
                'attr' => [
                    'class' => 'btn-danger'
                ]
            ])
            ->add('cancel', SubmitType::class, [
                'label' => 'Cancel',
                'validate' => false,
                'icon' => 'fa-times',
                'attr' => [
                    'class' => 'btn-default'
                ]
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulaformextensionsbundle_deletion';
    }
}
