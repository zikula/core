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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Translation\Extractor\Annotation\Ignore;
use Zikula\Bundle\FormExtensionBundle\Form\DataTransformer\NullToEmptyTransformer;
use Zikula\PermissionsModule\Entity\PermissionEntity;

class PermissionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pid', HiddenType::class)
            ->add('gid', ChoiceType::class, [
                'label' => 'Group',
                'choices' => /** @Ignore */array_flip($options['groups'])
            ])
            ->add('sequence', HiddenType::class)
            ->add('component', TextType::class, [
                'label' => 'Component'
            ])
            ->add('instance', TextType::class, [
                'label' => 'Instance'
            ])
            ->add('level', ChoiceType::class, [
                'label' => 'Level',
                'choices' => /** @Ignore */array_flip($options['permissionLevels'])
            ])
            ->add($builder->create('comment', TextType::class, [
                'label' => 'Comment',
                'required' => false
            ])->addModelTransformer(new NullToEmptyTransformer()))
            ->add($builder->create('colour', ChoiceType::class, [
                'label' => 'Colour',
                'choices' => [
                    'Default' => 'default',
                    'Active' => 'active',
                    'Primary' => 'primary',
                    'Secondary' => 'secondary',
                    'Success' => 'success',
                    'Danger' => 'danger',
                    'Warning' => 'warning',
                    'Info' => 'info',
                    'Light' => 'light',
                    'Dark' => 'dark'
                ],
                'required' => false
            ])->addModelTransformer(new NullToEmptyTransformer()))
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulapermissionsmodule_permission';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PermissionEntity::class,
            'groups' => [],
            'permissionLevels' => []
        ]);
    }
}
