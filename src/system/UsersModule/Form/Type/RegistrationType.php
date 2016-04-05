<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Validator\Constraints\ValidAntiSpamAnswer;
use Zikula\UsersModule\Validator\Constraints\ValidEmail;
use Zikula\UsersModule\Validator\Constraints\ValidPassword;
use Zikula\UsersModule\Validator\Constraints\ValidPasswordReminder;
use Zikula\UsersModule\Validator\Constraints\ValidUname;
use Zikula\UsersModule\Validator\Constraints\ValidUserFields;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('uname', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $options['translator']->__('User name'),
                'attr' => [
                    'class' => 'to-lower-case'
                ],
                'constraints' => [
                    new ValidUname(),
                ]
            ])
            ->add('pass', 'Symfony\Component\Form\Extension\Core\Type\RepeatedType', [
                'type' => 'Symfony\Component\Form\Extension\Core\Type\PasswordType',
                'first_options' => ['label' => $options['translator']->__('Password')],
                'second_options' => ['label' => $options['translator']->__('Repeat Password')],
                'invalid_message' => $options['translator']->__('The passwords must match!'),
                'constraints' => [
                    new ValidPassword()
                ]
            ])
        ;
        if ($options['passwordReminderEnabled']) {
            $builder
                ->add('passreminder', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                    'constraints' => [
                        new ValidPasswordReminder(),
                    ],
                    'required' => $options['passwordReminderMandatory']
                ]);
        }
        if ($options['includeEmail']) {
            $builder
                ->add('email', 'Symfony\Component\Form\Extension\Core\Type\RepeatedType', [
                    'type' => 'Symfony\Component\Form\Extension\Core\Type\EmailType',
                    'first_options' => ['label' => $options['translator']->__('Email')],
                    'second_options' => ['label' => $options['translator']->__('Repeat Email')],
                    'invalid_message' => $options['translator']->__('The emails  must match!'),
                    'constraints' => [
                        new ValidEmail(),
                    ]
                ]);
        }
        if (!empty($options['antiSpamQuestion'])) {
            $builder->add('antispamanswer', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $options['antiSpamQuestion'],
                'constraints' => new ValidAntiSpamAnswer()
            ]);
        }
        $builder
            ->add('submit', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $options['translator']->__('Save')
            ])
            ->add('cancel', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $options['translator']->__('Cancel')
            ])
            ->add('reset', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $options['translator']->__('Reset')
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulausersmodule_registration';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => null,
            'passwordReminderEnabled' => UsersConstant::DEFAULT_PASSWORD_REMINDER_ENABLED,
            'passwordReminderMandatory' => UsersConstant::DEFAULT_PASSWORD_REMINDER_MANDATORY,
            'antiSpamQuestion' => '',
            'includeEmail' => true,
            'constraints' => [new ValidUserFields()]
        ]);
    }
}
