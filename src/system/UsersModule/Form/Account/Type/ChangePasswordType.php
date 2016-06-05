<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Form\Account\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Validator\Constraints\ValidPassword;
use Zikula\UsersModule\Validator\Constraints\ValidPasswordChange;
use Zikula\UsersModule\Validator\Constraints\ValidPasswordReminder;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('uid', 'Symfony\Component\Form\Extension\Core\Type\HiddenType')
            ->add('oldpass', 'Symfony\Component\Form\Extension\Core\Type\PasswordType', [
                'required' => false,
                'label' => $options['translator']->__('Old password'),
                'input_group' => ['left' => '<i class="fa fa-asterisk"></i>']
            ])
            ->add('pass', 'Symfony\Component\Form\Extension\Core\Type\RepeatedType', [
                'type' => 'Symfony\Component\Form\Extension\Core\Type\PasswordType',
                'first_options' => ['label' => $options['translator']->__('New password')],
                'second_options' => ['label' => $options['translator']->__('Repeat new password')],
                'invalid_message' => $options['translator']->__('The passwords must match!'),
                'constraints' => [
                    new NotNull(),
                    new ValidPassword()
                ]
            ])
            ->add('submit', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $options['translator']->__('Save'),
                'icon' => 'fa-check',
                'attr' => ['class' => 'btn btn-success']
            ])
            ->add('cancel', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $options['translator']->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => ['class' => 'btn btn-default']
            ])
        ;
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

    public function getBlockPrefix()
    {
        return 'zikulausersmodule_changepassword';
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
            'constraints' => [new ValidPasswordChange()],
        ]);
    }
}
