<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
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

class PermissionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translator = $options['translator'];

        $builder
            ->add('pid', HiddenType::class)
            ->add('gid', ChoiceType::class, [
                'label' => $translator->__('Group'),
                'choices' => array_flip($options['groups']),
                'choices_as_values' => true
            ])
            ->add('sequence', HiddenType::class)
            ->add('component', TextType::class, [
                'label' => $translator->__('Component')
            ])
            ->add('instance', TextType::class, [
                'label' => $translator->__('Instance')
            ])
            ->add('level', ChoiceType::class, [
                'label' => $translator->__('Level'),
                'choices' => array_flip($options['permissionLevels']),
                'choices_as_values' => true
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikulapermissionsmodule_permission';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Zikula\PermissionsModule\Entity\PermissionEntity',
            'translator' => null,
            'groups' => [],
            'permissionLevels' => [],
        ]);
    }
}
