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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transportChoices = [
            'Sendmail message transfer agent' => 'sendmail',
            'Google gmail' => 'gmail',
            'SMTP mail transfer protocol' => 'smtp',
            'Development/debug mode (Do not send any email)' => 'test'/*'null'*/
        ];
        $transportAlert = null;

        // see https://swiftmailer.symfony.com/docs/sending.html
        if (!function_exists('proc_open')) {
            $transportChoices = [
                'Google gmail' => 'gmail',
                'Development/debug mode (Do not send any email)' => 'test'/*'null'*/
            ];
            $transportAlert = [
                'Mail transport mechanisms SMTP and SENDMAIL were disabled because your PHP does not allow the "proc_*" function. Either you need to remove it from the "disabled_functions" directive in your "php.ini" file or recompile your PHP entirely. Afterwards restart your webserver.' => 'warning'
            ];
        }

        $transportOptions = [
            'label' => 'Mail transport',
            'choices' => $transportChoices,
            'alert' => $transportAlert
        ];

        $builder
            ->add('transport', ChoiceType::class, $transportOptions)
            ->add('charset', TextType::class, [
                'label' => 'Character set',
                'attr' => [
                    'maxlength' => 20
                ],
                'help' => "Default: '%value%'",
                'help_translation_parameters' => [
                    '%value%' => $options['charset']
                ]
            ])
            ->add('encoding', ChoiceType::class, [
                'label' => 'Encoding',
                'choices' => [
                    '8bit' => '8bit',
                    '7bit' => '7bit',
                    'binary' => 'binary',
                    'base64' => 'base64',
                    'quoted-printable' => 'quoted-printable'
                ],
                'help' => "Default: '%value%'",
                'help_translation_parameters' => [
                    '%value%' => '8bit'
                ]
            ])
            ->add('html', CheckboxType::class, [
                'label' => 'HTML-formatted messages',
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false
            ])
            ->add('wordwrap', IntegerType::class, [
                'label' => 'Word wrap',
                'attr' => [
                    'maxlength' => 3
                ],
                'help' => "Default: '%value%'",
                'help_translation_parameters' => [
                    '%value%' => '50'
                ]
            ])
            ->add('enableLogging', CheckboxType::class, [
                'label' => 'Enable logging of sent mail',
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false
            ])
            ->add('host', TextType::class, [
                'label' => 'SMTP host server',
                'attr' => [
                    'maxlength' => 255
                ],
                'required' => false,
                'help' => "Default: '%value%'",
                'help_translation_parameters' => [
                    '%value%' => 'localhost'
                ]
            ])
            ->add('port', IntegerType::class, [
                'label' => 'SMTP port',
                'attr' => [
                    'maxlength' => 5
                ],
                'required' => false,
                'help' => "Default: '%value%'",
                'help_translation_parameters' => [
                    '%value%' => '25'
                ]
            ])
            ->add('encryption', ChoiceType::class, [
                'label' => 'SMTP encryption method',
                'choices' => [
                    'None' => '',
                    'SSL' => 'ssl',
                    'TLS' => 'tls'
                ],
                'required' => false
            ])
            ->add('auth_mode', ChoiceType::class, [
                'label' => 'SMTP authentication type',
                'choices' => [
                    'None' => '',
                    'Plain' => 'plain',
                    'Login' => 'login',
                    'Cram-MD5' => 'cram-md5'
                ],
                'required' => false
            ])
            ->add('username', TextType::class, [
                'label' => 'SMTP user name',
                'attr' => [
                    'maxlength' => 50
                ],
                'required' => false
            ])
            ->add('password', PasswordType::class, [
                'label' => 'SMTP password',
                'attr' => [
                    'maxlength' => 50
                ],
                'always_empty' => false,
                'required' => false
            ])
            ->add('usernameGmail', TextType::class, [
                'label' => 'Gmail user name',
                'attr' => [
                    'maxlength' => 50
                ],
                'required' => false
            ])
            ->add('passwordGmail', PasswordType::class, [
                'label' => 'Gmail password',
                'attr' => [
                    'maxlength' => 50
                ],
                'always_empty' => false,
                'required' => false
            ])
            ->add('save', SubmitType::class, [
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
        return 'zikulamailermodule_config';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'charset' => 'utf-8'
        ]);
    }
}
