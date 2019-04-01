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
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;

/**
 * Configuration form type class.
 */
class ConfigType extends AbstractType
{
    use TranslatorTrait;

    public function __construct(TranslatorInterface $translator)
    {
        $this->setTranslator($translator);
    }

    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transportChoices = [
            $this->__('Sendmail message transfer agent') => 'sendmail',
            $this->__('Google gmail') => 'gmail',
            $this->__('SMTP mail transfer protocol') => 'smtp',
            $this->__('Development/debug mode (Do not send any email)') => 'test'/*'null'*/
        ];
        $transportAlert = null;

        // see https://swiftmailer.symfony.com/docs/sending.html
        if (!function_exists('proc_open')) {
            $transportChoices = [
                $this->__('Google gmail') => 'gmail',
                $this->__('Development/debug mode (Do not send any email)') => 'test'/*'null'*/
            ];
            $transportAlert = [
                $this->__('Mail transport mechanisms SMTP and SENDMAIL were disabled because your PHP does not allow the "proc_*" function. Either you need to remove it from the "disabled_functions" directive in your "php.ini" file or recompile your PHP entirely. Afterwards restart your webserver.') => 'warning'
            ];
        }

        $transportOptions = [
            'label' => $this->__('Mail transport'),
            'choices' => $transportChoices,
            'alert' => $transportAlert
        ];

        $builder
            ->add('transport', ChoiceType::class, $transportOptions)
            ->add('charset', TextType::class, [
                'label' => $this->__('Character set'),
                'attr' => [
                    'maxlength' => 20
                ],
                'help' => $this->__f("Default: '%s'", ['%s' => $options['charset']])
            ])
            ->add('encoding', ChoiceType::class, [
                'label' => $this->__('Encoding'),
                'choices' => [
                    '8bit' => '8bit',
                    '7bit' => '7bit',
                    'binary' => 'binary',
                    'base64' => 'base64',
                    'quoted-printable' => 'quoted-printable'
                ],
                'help' => $this->__f("Default: '%s'", ['%s' => '8bit'])
            ])
            ->add('html', CheckboxType::class, [
                'label' => $this->__('HTML-formatted messages'),
                'required' => false
            ])
            ->add('wordwrap', IntegerType::class, [
                'label' => $this->__('Word wrap'),
                'scale' => 0,
                'attr' => [
                    'maxlength' => 3
                ],
                'help' => $this->__f("Default: '%s'", ['%s' => '50'])
            ])
            ->add('enableLogging', CheckboxType::class, [
                'label' => $this->__('Enable logging of sent mail'),
                'required' => false
            ])
            ->add('host', TextType::class, [
                'label' => $this->__('SMTP host server'),
                'attr' => [
                    'maxlength' => 255
                ],
                'required' => false,
                'help' => $this->__f("Default: '%s'", ['%s' => 'localhost'])
            ])
            ->add('port', IntegerType::class, [
                'label' => $this->__('SMTP port'),
                'scale' => 0,
                'attr' => [
                    'maxlength' => 5
                ],
                'required' => false,
                'help' => $this->__f("Default: '%s'", ['%s' => '25'])
            ])
            ->add('encryption', ChoiceType::class, [
                'label' => $this->__('SMTP encryption method'),
                'choices' => [
                    $this->__('None') => '',
                    'SSL' => 'ssl',
                    'TLS' => 'tls'
                ],
                'required' => false
            ])
            ->add('auth_mode', ChoiceType::class, [
                'label' => $this->__('SMTP authentication type'),
                'choices' => [
                    $this->__('None') => '',
                    'Plain' => 'plain',
                    'Login' => 'login',
                    'Cram-MD5' => 'cram-md5'
                ],
                'required' => false
            ])
            ->add('username', TextType::class, [
                'label' => $this->__('SMTP user name'),
                'attr' => [
                    'maxlength' => 50
                ],
                'required' => false
            ])
            ->add('password', PasswordType::class, [
                'label' => $this->__('SMTP password'),
                'attr' => [
                    'maxlength' => 50
                ],
                'always_empty' => false,
                'required' => false
            ])
            ->add('usernameGmail', TextType::class, [
                'label' => $this->__('Gmail user name'),
                'attr' => [
                    'maxlength' => 50
                ],
                'required' => false
            ])
            ->add('passwordGmail', PasswordType::class, [
                'label' => $this->__('Gmail password'),
                'attr' => [
                    'maxlength' => 50
                ],
                'always_empty' => false,
                'required' => false
            ])
            ->add('save', SubmitType::class, [
                'label' => $this->__('Save'),
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
            ->add('cancel', SubmitType::class, [
                'label' => $this->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => [
                    'class' => 'btn btn-default'
                ]
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
