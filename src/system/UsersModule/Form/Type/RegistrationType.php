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

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', 'Zikula\UsersModule\Form\Type\UserType', [
                'translator' => $options['translator'],
                'passwordReminderEnabled' => $options['passwordReminderEnabled'],
                'passwordReminderMandatory' => $options['passwordReminderMandatory']
            ])
            ->add('submit', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $options['translator']->__('Save'),
                'icon' => 'fa-plus',
                'attr' => ['class' => 'btn btn-success']
            ])
            ->add('cancel', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $options['translator']->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => ['class' => 'btn btn-danger']
            ])
            ->add('reset', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $options['translator']->__('Reset'),
                'icon' => 'fa-refresh',
                'attr' => ['class' => 'btn btn-primary']
            ])
        ;
        if (!$options['includeEmail']) {
            $builder->get('user')->remove('email');
        }
        if (!empty($options['antiSpamQuestion'])) {
            $builder->add('antispamanswer', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'mapped' => false,
                'label' => $options['antiSpamQuestion'],
                'constraints' => new ValidAntiSpamAnswer(),
                'help' => $options['translator']->__('Asking this question helps us prevent automated scripts from accessing private areas of the site.'),
            ]);
        }
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
        ]);
    }
}
