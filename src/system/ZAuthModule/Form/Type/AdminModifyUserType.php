<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ZAuthModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\UsersModule\Validator\Constraints\ValidEmail;
use Zikula\UsersModule\Validator\Constraints\ValidUname;
use Zikula\ZAuthModule\Validator\Constraints\ValidPassword;
use Zikula\ZAuthModule\Validator\Constraints\ValidUserFields;

class AdminModifyUserType extends AbstractType
{
    use TranslatorTrait;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->setTranslator($translator);
    }

    /**
     * @param TranslatorInterface $translator
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('uname', TextType::class, [
                'label' => $this->__('User name'),
                'help' => $this->__('User names can contain letters, numbers, underscores, periods, spaces and/or dashes.'),
                'constraints' => [
                    new ValidUname()
                ]
            ])
            ->add('email', RepeatedType::class, [
                'type' => EmailType::class,
                'first_options' => [
                    'label' => $this->__('Email'),
                ],
                'second_options' => [
                    'label' => $this->__('Repeat Email')
                ],
                'invalid_message' => $this->__('The emails must match!'),
                'constraints' => [
                    new ValidEmail()
                ]
            ])
            ->add('setpass', CheckboxType::class, [
                'required' => false,
                'mapped' => false,
                'label' => $this->__('Set password now'),
            ])
            ->add('pass', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'required' => false,
                    'label' => $this->__('Create new password'),
                    'input_group' => ['left' => '<i class="fa fa-asterisk"></i>'],
                    'help' => $this->__f('Minimum password length: %amount% characters.', ['%amount%' => $options['minimumPasswordLength']])
                ],
                'second_options' => [
                    'required' => false,
                    'label' => $this->__('Repeat new password'),
                    'input_group' => ['left' => '<i class="fa fa-asterisk"></i>']
                ],
                'invalid_message' => $this->__('The passwords must match!'),
                'constraints' => [
                    new ValidPassword()
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->__('Save'),
                'icon' => 'fa-check',
                'attr' => ['class' => 'btn btn-success']
            ])
            ->add('cancel', SubmitType::class, [
                'label' => $this->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => ['class' => 'btn btn-default']
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikulazauthmodule_adminmodifyuser';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'minimumPasswordLength' => 5,
            'constraints' => [
                new ValidUserFields()
            ]
        ]);
    }
}
