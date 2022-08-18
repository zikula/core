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

namespace Zikula\ProfileModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Type;
use Zikula\ProfileModule\ProfileConstant;

class ConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('viewregdate', CheckboxType::class, [
                'label' => 'Display the user\'s registration date',
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false
            ])
            ->add('memberslistitemsperpage', IntegerType::class, [
                'constraints' => [new Range(['min' => 10, 'max' => 999])],
                'label' => 'Users per page in \'Registered users list\'',
                'attr' => [
                    'maxlength' => 3
                ]
            ])
            ->add('onlinemembersitemsperpage', IntegerType::class, [
                'constraints' => [new Range(['min' => 10, 'max' => 999])],
                'label' => 'Users per page in \'Users currently on-line\' page',
                'attr' => [
                    'maxlength' => 3
                ]
            ])
            ->add('recentmembersitemsperpage', IntegerType::class, [
                'constraints' => [new Range(['min' => 10, 'max' => 999])],
                'label' => 'Users per page in \'Recent registrations\' page',
                'attr' => [
                    'maxlength' => 3
                ]
            ])
            ->add('activeminutes', IntegerType::class, [
                'constraints' => [new Range(['min' => 1, 'max' => 99])],
                'label' => 'Minutes a user is considered online',
                'attr' => [
                    'maxlength' => 2
                ]
            ])
            ->add('filterunverified', CheckboxType::class, [
                'label' => 'Filter unverified users from \'Registered users list\'',
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false
            ])
            ->add(ProfileConstant::MODVAR_AVATAR_IMAGE_PATH, TextType::class, [
                'label' => 'Path to user\'s avatar images',
                'constraints' => [
                    new NotBlank(),
                    new Type('string')
                ]
            ])
            ->add(ProfileConstant::MODVAR_GRAVATARS_ENABLED, ChoiceType::class, [
                'label' => 'Allow usage of Gravatar',
                'label_attr' => ['class' => 'radio-custom'],
                'choices' => [
                    'Yes' => 1,
                    'No' => 0
                ],
                'expanded' => true
            ])
            ->add(ProfileConstant::MODVAR_GRAVATAR_IMAGE, TextType::class, [
                'label' => 'Default avatar image (used as fallback)',
                'constraints' => [
                    new NotBlank(),
                    new Type('string')
                ]
            ])
            ->add('allowUploads', CheckboxType::class, [
                'label' => 'Allow uploading custom avatar images',
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false
            ])
            ->add('shrinkLargeImages', CheckboxType::class, [
                'label' => 'Shrink large images to maximum dimensions',
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false
            ])
            ->add('maxSize', IntegerType::class, [
                'label' => 'Max. avatar filesize',
                'input_group' => ['right' => 'bytes']
            ])
            ->add('maxWidth', IntegerType::class, [
                'label' => 'Max. width',
                'attr' => [
                    'maxlength' => 4
                ],
                'input_group' => ['right' => 'pixels']
            ])
            ->add('maxHeight', IntegerType::class, [
                'label' => 'Max. height',
                'attr' => [
                    'maxlength' => 4
                ],
                'input_group' => ['right' => 'pixels']
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save',
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
        return 'zikulaprofilemodule_config';
    }
}
