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

namespace Zikula\PermissionsModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Translation\Extractor\Annotation\Ignore;

class PermissionCheckType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', TextType::class, [
                'label' => 'User name',
                'required' => false
            ])
            ->add('component', TextType::class, [
                'label' => 'Component to check',
                'data' => '.*'
            ])
            ->add('instance', TextType::class, [
                'label' => 'Instance to check',
                'data' => '.*'
            ])
            ->add('level', ChoiceType::class, [
                'label' => 'Permission level',
                'choices' => /** @Ignore */array_flip($options['permissionLevels']),
                'data' => ACCESS_READ
            ])
            ->add('check', ButtonType::class, [
                'label' => 'Check permission',
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn-primary'
                ]
            ])
            ->add('reset', ButtonType::class, [
                'label' => 'Reset',
                'icon' => 'fa-times',
                'attr' => [
                    'class' => 'btn-warning'
                ]
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulapermissionsmodule_permissioncheck';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => ['id' => 'testpermform'],
            'permissionLevels' => []
        ]);
    }
}
