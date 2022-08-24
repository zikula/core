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
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;
use Zikula\ZAuthBundle\Validator\Constraints\ValidPassword;
use Zikula\ZAuthBundle\Validator\Constraints\ValidRegistrationVerification;

class VerifyRegistrationType extends AbstractType
{
    public function __construct(private readonly int $minimumPasswordLength)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('uname', TextType::class, [
                'label' => 'User name',
                'constraints' => [
                    new NotBlank(),
                    new Type(['type' => 'string']),
                ],
            ])
            ->add('verifycode', TextType::class, [
                'label' => 'Verification code',
                'constraints' => [
                    new NotBlank(),
                    new Type(['type' => 'string']),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Submit',
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn-success',
                ],
            ])
        ;
        if ($options['setpass']) {
            $builder->add('pass', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'attr' => [
                        'class' => 'pwstrength',
                        'data-uname-id' => $builder->getName() . '_' . $builder->get('uname')->getName(),
                        'minlength' => $options['minimumPasswordLength'],
                        'pattern' => '.{' . $options['minimumPasswordLength'] . ',}',
                    ],
                    'label' => 'Password',
                ],
                'second_options' => ['label' => 'Repeat password'],
                'invalid_message' => 'The passwords must match!',
                'constraints' => [
                    new NotNull(),
                    new ValidPassword(),
                ],
            ]);
        }
    }

    public function getBlockPrefix()
    {
        return 'zikulausersbundle_verifyregistration';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'setpass' => true,
            'minimumPasswordLength' => $this->minimumPasswordLength,
            'constraints' => [
                new ValidRegistrationVerification(),
            ],
        ]);
    }
}
