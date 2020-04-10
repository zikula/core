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
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ZAuthModule\Validator\Constraints\ValidPassword;
use Zikula\ZAuthModule\Validator\Constraints\ValidPasswordChange;
use Zikula\ZAuthModule\ZAuthConstant;

class ChangePasswordType extends AbstractType
{
    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    public function __construct(VariableApiInterface $variableApi)
    {
        $this->variableApi = $variableApi;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('uid', HiddenType::class)
            ->add('login', HiddenType::class)
            ->add('authenticationMethod', HiddenType::class)
            ->add('oldpass', PasswordType::class, [
                'required' => false,
                'label' => 'Old password',
                'input_group' => ['left' => '<i class="fas fa-asterisk"></i>']
            ])
            ->add('pass', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'attr' => [
                        'class' => 'pwstrength',
                        'data-uname-id' => '',
                        'minlength' => $options['minimumPasswordLength'],
                        'pattern' => '.{' . $options['minimumPasswordLength'] . ',}'
                    ],
                    'label' => 'New password',
                    'help' => 'Minimum password length: %amount% characters.',
                    'help_translation_parameters' => [
                        '%amount%' => $options['minimumPasswordLength']
                    ]
                ],
                'second_options' => [
                    'label' => 'Repeat new password'
                ],
                'invalid_message' => 'The passwords must match!',
                'constraints' => [
                    new NotNull(),
                    new ValidPassword()
                ]
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

    public function getBlockPrefix()
    {
        return 'zikulazauthmodule_changepassword';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'minimumPasswordLength' => $this->variableApi->get('ZikulaZAuthModule', ZAuthConstant::MODVAR_PASSWORD_MINIMUM_LENGTH, ZAuthConstant::PASSWORD_MINIMUM_LENGTH),
            'constraints' => [
                new ValidPasswordChange()
            ]
        ]);
    }
}
