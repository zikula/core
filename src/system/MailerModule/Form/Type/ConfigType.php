<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\MailerModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Configuration form type class.
 */
class ConfigType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translator = $options['translator'];

        $transportChoices = [
            $translator->__('Internal PHP `mail()` function') => 'mail',
            $translator->__('Sendmail message transfer agent') => 'sendmail',
            $translator->__('Google gmail') => 'gmail',
            $translator->__('SMTP mail transfer protocol') => 'smtp',
            $translator->__('Development/debug mode (Do not send any email)') => 'test'/*'null'*/
        ];
        $transportAlert = null;

        // see http://swiftmailer.org/docs/sending.html
        if (!function_exists('proc_open')) {
            $transportChoices = [
                $translator->__('Internal PHP `mail()` function') => 'mail',
                $translator->__('Google gmail') => 'gmail',
                $translator->__('Development/debug mode (Do not send any email)') => 'test'/*'null'*/
            ];
            $transportAlert = [
                $translator->__('Mail transport mechanisms SMTP and SENDMAIL were disabled because your PHP does not allow the "proc_*" function. Either you need to remove it from the "disabled_functions" directive in your "php.ini" file or recompile your PHP entirely. Afterwards restart your webserver.') => 'warning'
            ];
        }

        $transportOptions = [
            'label' => $translator->__('Mail transport'),
            'choices' => $transportChoices,
            'choices_as_values' => true,
            'alert' => $transportAlert
        ];

        $builder
            ->add('transport', ChoiceType::class, $transportOptions)
            ->add('charset', TextType::class, [
                'label' => $translator->__('Character set'),
                'attr' => [
                    'maxlength' => 20
                ],
                'help' => $translator->__f("Default: '%s'", ['%s' => $options['charset']])
            ])
            ->add('encoding', ChoiceType::class, [
                'label' => $translator->__('Encoding'),
                'choices' => [
                    '8bit' => '8bit',
                    '7bit' => '7bit',
                    'binary' => 'binary',
                    'base64' => 'base64',
                    'quoted-printable' => 'quoted-printable'
                ],
                'choices_as_values' => true,
                'help' => $translator->__f("Default: '%s'", ['%s' => '8bit'])
            ])
            ->add('html', CheckboxType::class, [
                'label' => $translator->__('HTML-formatted messages'),
                'required' => false
            ])
            ->add('wordwrap', IntegerType::class, [
                'label' => $translator->__('Word wrap'),
                'scale' => 0,
                'attr' => [
                    'maxlength' => 3
                ],
                'help' => $translator->__f("Default: '%s'", ['%s' => '50'])
            ])
            ->add('enableLogging', CheckboxType::class, [
                'label' => $translator->__('Enable logging of sent mail'),
                'required' => false
            ])
            ->add('host', TextType::class, [
                'label' => $translator->__('SMTP host server'),
                'attr' => [
                    'maxlength' => 255
                ],
                'required' => false,
                'help' => $translator->__f("Default: '%s'", ['%s' => 'localhost'])
            ])
            ->add('port', IntegerType::class, [
                'label' => $translator->__('SMTP port'),
                'scale' => 0,
                'attr' => [
                    'maxlength' => 5
                ],
                'required' => false,
                'help' => $translator->__f("Default: '%s'", ['%s' => '25'])
            ])
            ->add('encryption', ChoiceType::class, [
                'label' => $translator->__('SMTP encryption method'),
                'choices' => [
                    $translator->__('None') => '',
                    'SSL' => 'ssl',
                    'TLS' => 'tls'
                ],
                'choices_as_values' => true,
                'required' => false
            ])
            ->add('auth_mode', ChoiceType::class, [
                'label' => $translator->__('SMTP authentication type'),
                'choices' => [
                    $translator->__('None') => '',
                    'Plain' => 'plain',
                    'Login' => 'login',
                    'Cram-MD5' => 'cram-md5'
                ],
                'choices_as_values' => true,
                'required' => false
            ])
            ->add('username', TextType::class, [
                'label' => $translator->__('SMTP user name'),
                'attr' => [
                    'maxlength' => 50
                ],
                'required' => false
            ])
            ->add('password', PasswordType::class, [
                'label' => $translator->__('SMTP password'),
                'attr' => [
                    'maxlength' => 50
                ],
                'always_empty' => false,
                'required' => false
            ])
            ->add('usernameGmail', TextType::class, [
                'label' => $translator->__('Gmail user name'),
                'attr' => [
                    'maxlength' => 50
                ],
                'required' => false
            ])
            ->add('passwordGmail', PasswordType::class, [
                'label' => $translator->__('Gmail password'),
                'attr' => [
                    'maxlength' => 50
                ],
                'always_empty' => false,
                'required' => false
            ])
            ->add('save', SubmitType::class, [
                'label' => $translator->__('Save'),
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
            ->add('cancel', SubmitType::class, [
                'label' => $translator->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => [
                    'class' => 'btn btn-default'
                ]
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikulamailermodule_config';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => null,
            'charset' => 'utf-8'
        ]);
    }
}
