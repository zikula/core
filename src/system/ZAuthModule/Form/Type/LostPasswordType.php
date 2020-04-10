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

namespace Zikula\ZAuthModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Zikula\ZAuthModule\Validator\Constraints\ValidPassword;

class LostPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$options['includeReset']) {
            $builder
                ->add('uname', TextType::class, [
                    'required' => false,
                    'label' => 'User name',
                    'input_group' => ['left' => '<i class="fas fa-user"></i>'],
                ])
                ->add('email', EmailType::class, [
                    'required' => false,
                    'label' => 'Email address',
                    'input_group' => ['left' => '<i class="fas fa-at"></i>'],
                ])
            ;
        } else {
            $builder
                ->add('pass', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'first_options' => [
                        'label' => 'Create new password',
                        'input_group' => ['left' => '<i class="fas fa-asterisk"></i>']
                    ],
                    'second_options' => [
                        'label' => 'Repeat new password',
                        'input_group' => ['left' => '<i class="fas fa-asterisk"></i>']
                    ],
                    'invalid_message' => 'The passwords must match!',
                    'constraints' => [
                        new NotNull(),
                        new ValidPassword()
                    ]
                ])
            ;
        }
        $builder
            ->add('submit', SubmitType::class, [
                'label' => 'Submit',
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn-success'
                ]
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulazauthmodule_account_lostpassword';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'includeReset' => false,
            'constraints' => new Callback(['callback' => static function($data, ExecutionContextInterface $context) {
                if (!isset($data['pass']) && empty($data['uname']) && empty($data['email'])) {
                    $context
                        ->buildViolation('Error! You must enter either your username or email address.')
                        ->addViolation()
                    ;
                }
            }]),
        ]);
    }
}
