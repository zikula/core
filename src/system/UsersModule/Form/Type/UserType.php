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

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('uname', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $options['translator']->__('User name'),
                'help' => $options['translator']->__('User names can contain letters, numbers, underscores, periods and/or dashes.'),
                'attr' => [
                    'class' => 'to-lower-case'
                ]
            ])
            ->add('pass', 'Symfony\Component\Form\Extension\Core\Type\RepeatedType', [
                'type' => 'Symfony\Component\Form\Extension\Core\Type\PasswordType',
                'first_options' => ['label' => $options['translator']->__('Password')],
                'second_options' => ['label' => $options['translator']->__('Repeat Password')],
                'invalid_message' => $options['translator']->__('The passwords must match!'),
            ])
            ->add('email', 'Symfony\Component\Form\Extension\Core\Type\RepeatedType', [
                'type' => 'Symfony\Component\Form\Extension\Core\Type\EmailType',
                'first_options' => [
                    'label' => $options['translator']->__('Email'),
                    'help' => $options['translator']->__('You will use your e-mail address to identify yourself when you log in.'),
                ],
                'second_options' => ['label' => $options['translator']->__('Repeat Email')],
                'invalid_message' => $options['translator']->__('The emails  must match!'),
            ])
            // theme - deprecated
            // ublock on? - should be deprecated
            // time zone
            // locale i18n
        ;
        if ($options['passwordReminderEnabled']) {
            $builder
                ->add('passreminder', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                    'required' => $options['passwordReminderMandatory'],
                    'help' => $options['translator']->__('Enter a word or a phrase that will remind you of your password.'),
                    'alert' => [$options['translator']->__('Notice: Do not use a word or phrase that will allow others to guess your password! Do not include your password or any part of your password here!') => 'info'],
                ])
            ;
        }
    }

    public function getBlockPrefix()
    {
        return 'zikulausersmodule_user';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'inherit_data' => true,
            'translator' => null,
            'passwordReminderEnabled' => UsersConstant::DEFAULT_PASSWORD_REMINDER_ENABLED,
            'passwordReminderMandatory' => UsersConstant::DEFAULT_PASSWORD_REMINDER_MANDATORY
        ]);
    }
}
