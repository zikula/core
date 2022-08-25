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

namespace Zikula\ZAuthBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\UsersBundle\Validator\Constraints\ValidEmail;
use Zikula\UsersBundle\Validator\Constraints\ValidUname;
use Zikula\ZAuthBundle\Entity\AuthenticationMappingEntity;
use Zikula\ZAuthBundle\Validator\Constraints\ValidUserFields;

class AdminModifyUserType extends AbstractType
{
    public function __construct(private readonly int $minimumPasswordLength)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('uname', TextType::class, [
                'label' => 'User name',
                'help' => 'User names can contain letters, numbers, underscores, periods, spaces and/or dashes.',
                'constraints' => [
                    new ValidUname()
                ]
            ])
            ->add('email', RepeatedType::class, [
                'type' => EmailType::class,
                'first_options' => [
                    'label' => 'Email',
                ],
                'second_options' => [
                    'label' => 'Repeat email'
                ],
                'invalid_message' => 'The emails must match!',
                'constraints' => [
                    new ValidEmail()
                ]
            ])
            ->add('setpass', CheckboxType::class, [
                'required' => false,
                'mapped' => false,
                'label' => 'Set password now',
                'label_attr' => ['class' => 'switch-custom']
            ])
            ->add('pass', ZAuthDuplicatePassType::class, [
                'label' => 'Password',
                'data_class' => AuthenticationMappingEntity::class,
                'dataUnameId' => $builder->getName() . '_' . $builder->get('uname')->getName()
            ])
            ->add('submit', SubmitType::class, [
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

    public function getBlockPrefix(): string
    {
        return 'zikulazauthbundle_adminmodifyuser';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'minimumPasswordLength' => $this->minimumPasswordLength,
            'constraints' => [
                new ValidUserFields(),
            ],
        ]);
    }
}
