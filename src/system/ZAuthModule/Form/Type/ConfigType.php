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
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Zikula\ZAuthModule\ZAuthConstant;

class ConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(ZAuthConstant::MODVAR_PASSWORD_MINIMUM_LENGTH, 'Symfony\Component\Form\Extension\Core\Type\IntegerType', [
                'label' => $options['translator']->__('Minimum length for user passwords'),
                'required' => false,
                'help' => $options['translator']->__('This affects both passwords created during registration, as well as passwords modified by users or administrators. Enter an integer greater than zero.'),
                'constraints' => [
                    new NotBlank(),
                    new GreaterThanOrEqual(['value' => 3]) // @todo
                ]
            ])
            ->add(ZAuthConstant::MODVAR_HASH_METHOD, 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $options['translator']->__('Password hashing method'),
                'help' => $options['translator']->__('The default hashing method is \'SHA256\'.'), //@todo
                'choices' => [
                    'SHA1'  => 'sha1',
                    'SHA256' => 'sha256',
                    // @todo bcrypt
                ],
                'choices_as_values' => true
            ])
            ->add(ZAuthConstant::MODVAR_PASSWORD_STRENGTH_METER_ENABLED, 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $options['translator']->__('Show password strength meter'),
                'required' => false,
            ])
//            ->add(UsersConstant::MODVAR_EXPIRE_DAYS_CHANGE_EMAIL, 'Symfony\Component\Form\Extension\Core\Type\IntegerType', [
//                'label' => $options['translator']->__('E-mail address verifications expire in'),
//                'help' => $options['translator']->__('Enter the number of days a user\'s request to change e-mail addresses should be kept while waiting for verification. Enter zero (0) for no expiration.'),
//                'input_group' => ['right' => $options['translator']->__('days')],
//                'alert' => [
//                    $options['translator']->__('Changing this setting will affect all requests to change e-mail addresses currently pending verification.') => 'warning'
//                ],
//                'constraints' => [
//                    new NotBlank(),
//                    new GreaterThanOrEqual(['value' => 0])
//                ]
//            ])
//            ->add(UsersConstant::MODVAR_EXPIRE_DAYS_CHANGE_PASSWORD, 'Symfony\Component\Form\Extension\Core\Type\IntegerType', [
//                'label' => $options['translator']->__('Password reset requests expire in'),
//                'help' => $options['translator']->__('This setting only affects users who have not established security question responses. Enter the number of days a user\'s request to reset a password should be kept while waiting for verification. Enter zero (0) for no expiration.'),
//                'input_group' => ['right' => $options['translator']->__('days')],
//                'alert' => [
//                    $options['translator']->__('Changing this setting will affect all password change requests currently pending verification.') => 'warning'
//                ],
//                'constraints' => [
//                    new NotBlank(),
//                    new GreaterThanOrEqual(['value' => 0])
//                ]
//            ])
            ->add(ZAuthConstant::MODVAR_PASSWORD_REMINDER_ENABLED, 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $options['translator']->__('Password reminder is enabled'),
                'required' => false,
            ])
            ->add(ZAuthConstant::MODVAR_PASSWORD_REMINDER_MANDATORY, 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $options['translator']->__('Password reminder is mandatory'),
                'required' => false,
            ])
            /**
             * Buttons
             */
            ->add('save', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
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
    }

    public function getBlockPrefix()
    {
        return 'zikulazauthmodule_config';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => null,
        ]);
    }
}
