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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Configuration form type class.
 */
class ConfigType extends AbstractType
{
    /**
* @inheritDoc
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
            ->add('transport', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', $transportOptions)
            ->add('charset', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $translator->__('Character set'),
                'attr' => [
                    'maxlength' => 20
                ],
                'help' => $translator->__f("Default: '%s'", ['%s' => $options['charset']])
            ])
            ->add('encoding', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
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
            ->add('html', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $translator->__('HTML-formatted messages'),
                'required' => false
            ])
            ->add('wordwrap', 'Symfony\Component\Form\Extension\Core\Type\IntegerType', [
                'label' => $translator->__('Word wrap'),
                'scale' => 0,
                'attr' => [
                    'maxlength' => 3
                ],
                'help' => $translator->__f("Default: '%s'", ['%s' => '50'])
            ])
            ->add('enableLogging', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $translator->__('Enable logging of sent mail'),
                'required' => false
            ])
            ->add('host', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $translator->__('SMTP host server'),
                'attr' => [
                    'maxlength' => 255
                ],
                'required' => false,
                'help' => $translator->__f("Default: '%s'", ['%s' => 'localhost'])
            ])
            ->add('port', 'Symfony\Component\Form\Extension\Core\Type\IntegerType', [
                'label' => $translator->__('SMTP port'),
                'scale' => 0,
                'attr' => [
                    'maxlength' => 5
                ],
                'required' => false,
                'help' => $translator->__f("Default: '%s'", ['%s' => '25'])
            ])
            ->add('encryption', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $translator->__('SMTP encryption method'),
                'choices' => [
                    $translator->__('None') => '',
                    'SSL' => 'ssl',
                    'TLS' => 'tls'
                ],
                'choices_as_values' => true,
                'required' => false
            ])
            ->add('auth_mode', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
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
            ->add('username', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $translator->__('SMTP user name'),
                'attr' => [
                    'maxlength' => 50
                ],
                'required' => false
            ])
            ->add('password', 'Symfony\Component\Form\Extension\Core\Type\PasswordType', [
                'label' => $translator->__('SMTP password'),
                'attr' => [
                    'maxlength' => 50
                ],
                'always_empty' => false,
                'required' => false
            ])
            ->add('usernameGmail', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $translator->__('Gmail user name'),
                'attr' => [
                    'maxlength' => 50
                ],
                'required' => false
            ])
            ->add('passwordGmail', 'Symfony\Component\Form\Extension\Core\Type\PasswordType', [
                'label' => $translator->__('Gmail password'),
                'attr' => [
                    'maxlength' => 50
                ],
                'always_empty' => false,
                'required' => false
            ])
            ->add('save', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $translator->__('Save'),
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
            ->add('cancel', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $translator->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => [
                    'class' => 'btn btn-default'
                ]
            ])
        ;
    }

    /**
* @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'zikulamailermodule_config';
    }

    /**
* @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => null,
            'charset' => 'utf-8'
        ]);
    }
}
