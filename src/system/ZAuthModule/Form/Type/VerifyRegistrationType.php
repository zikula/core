<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ZAuthModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Validator\Constraints\ValidPassword;
use Zikula\UsersModule\Validator\Constraints\ValidPasswordReminder;
use Zikula\UsersModule\Validator\Constraints\ValidRegistrationVerification;
use Zikula\ZAuthModule\ZAuthConstant;

class VerifyRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('uname', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $options['translator']->__('User name'),
                'constraints' => [
                    new NotBlank(),
                    new Type(['type' => 'string'])
                ]
            ])
            ->add('verifycode', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $options['translator']->__('Verification code'),
                'constraints' => [
                    new NotBlank(),
                    new Type(['type' => 'string'])
                ]
            ])
            ->add('submit', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $options['translator']->__('Submit'),
                'icon' => 'fa-check',
                'attr' => ['class' => 'btn btn-success']
            ])
        ;
        if ($options['setpass']) {
            $builder
                ->add('pass', 'Symfony\Component\Form\Extension\Core\Type\RepeatedType', [
                    'type' => 'Symfony\Component\Form\Extension\Core\Type\PasswordType',
                    'first_options' => ['label' => $options['translator']->__('Password')],
                    'second_options' => ['label' => $options['translator']->__('Repeat Password')],
                    'invalid_message' => $options['translator']->__('The passwords must match!'),
                    'constraints' => [
                        new NotNull(),
                        new ValidPassword()
                    ]
                ]);
            if ($options['passwordReminderEnabled']) {
                $builder
                    ->add('passreminder', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                        'required' => $options['passwordReminderMandatory'],
                        'help' => $options['translator']->__('Enter a word or a phrase that will remind you of your password.'),
                        'alert' => [$options['translator']->__('Notice: Do not use a word or phrase that will allow others to guess your password! Do not include your password or any part of your password here!') => 'info'],
                        'constraints' => [new ValidPasswordReminder()]
                    ])
                ;
            }
        }
    }

    public function getBlockPrefix()
    {
        return 'zikulausersmodule_verifyregistration';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => null,
            'setpass' => true,
            'passwordReminderEnabled' => ZAuthConstant::DEFAULT_PASSWORD_REMINDER_ENABLED,
            'passwordReminderMandatory' => ZAuthConstant::DEFAULT_PASSWORD_REMINDER_MANDATORY,
            'constraints' => [
                new ValidRegistrationVerification(),
            ]
        ]);
    }
}
