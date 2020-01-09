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
use Symfony\Contracts\Translation\TranslatorInterface;
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

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transportChoices = [
            $this->trans('Sendmail message transfer agent') => 'sendmail',
            $this->trans('Google gmail') => 'gmail',
            $this->trans('SMTP mail transfer protocol') => 'smtp',
            $this->trans('Development/debug mode (Do not send any email)') => 'test'/*'null'*/
        ];
        $transportAlert = null;

        // see https://swiftmailer.symfony.com/docs/sending.html
        if (!function_exists('proc_open')) {
            $transportChoices = [
                $this->trans('Google gmail') => 'gmail',
                $this->trans('Development/debug mode (Do not send any email)') => 'test'/*'null'*/
            ];
            $transportAlert = [
                $this->trans('Mail transport mechanisms SMTP and SENDMAIL were disabled because your PHP does not allow the "proc_*" function. Either you need to remove it from the "disabled_functions" directive in your "php.ini" file or recompile your PHP entirely. Afterwards restart your webserver.') => 'warning'
            ];
        }

        $transportOptions = [
            'label' => $this->trans('Mail transport'),
            'choices' => $transportChoices,
            'alert' => $transportAlert
        ];

        $builder
            ->add('transport', ChoiceType::class, $transportOptions)
            ->add('charset', TextType::class, [
                'label' => $this->trans('Character set'),
                'attr' => [
                    'maxlength' => 20
                ],
                'help' => $this->trans("Default: '%s'", ['%s' => $options['charset']])
            ])
            ->add('encoding', ChoiceType::class, [
                'label' => $this->trans('Encoding'),
                'choices' => [
                    '8bit' => '8bit',
                    '7bit' => '7bit',
                    'binary' => 'binary',
                    'base64' => 'base64',
                    'quoted-printable' => 'quoted-printable'
                ],
                'help' => $this->trans("Default: '%s'", ['%s' => '8bit'])
            ])
            ->add('html', CheckboxType::class, [
                'label' => $this->trans('HTML-formatted messages'),
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false
            ])
            ->add('wordwrap', IntegerType::class, [
                'label' => $this->trans('Word wrap'),
                'attr' => [
                    'maxlength' => 3
                ],
                'help' => $this->trans("Default: '%s'", ['%s' => '50'])
            ])
            ->add('enableLogging', CheckboxType::class, [
                'label' => $this->trans('Enable logging of sent mail'),
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false
            ])
            ->add('host', TextType::class, [
                'label' => $this->trans('SMTP host server'),
                'attr' => [
                    'maxlength' => 255
                ],
                'required' => false,
                'help' => $this->trans("Default: '%s'", ['%s' => 'localhost'])
            ])
            ->add('port', IntegerType::class, [
                'label' => $this->trans('SMTP port'),
                'attr' => [
                    'maxlength' => 5
                ],
                'required' => false,
                'help' => $this->trans("Default: '%s'", ['%s' => '25'])
            ])
            ->add('encryption', ChoiceType::class, [
                'label' => $this->trans('SMTP encryption method'),
                'choices' => [
                    $this->trans('None') => '',
                    'SSL' => 'ssl',
                    'TLS' => 'tls'
                ],
                'required' => false
            ])
            ->add('auth_mode', ChoiceType::class, [
                'label' => $this->trans('SMTP authentication type'),
                'choices' => [
                    $this->trans('None') => '',
                    'Plain' => 'plain',
                    'Login' => 'login',
                    'Cram-MD5' => 'cram-md5'
                ],
                'required' => false
            ])
            ->add('username', TextType::class, [
                'label' => $this->trans('SMTP user name'),
                'attr' => [
                    'maxlength' => 50
                ],
                'required' => false
            ])
            ->add('password', PasswordType::class, [
                'label' => $this->trans('SMTP password'),
                'attr' => [
                    'maxlength' => 50
                ],
                'always_empty' => false,
                'required' => false
            ])
            ->add('usernameGmail', TextType::class, [
                'label' => $this->trans('Gmail user name'),
                'attr' => [
                    'maxlength' => 50
                ],
                'required' => false
            ])
            ->add('passwordGmail', PasswordType::class, [
                'label' => $this->trans('Gmail password'),
                'attr' => [
                    'maxlength' => 50
                ],
                'always_empty' => false,
                'required' => false
            ])
            ->add('save', SubmitType::class, [
                'label' => $this->trans('Save'),
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
            ->add('cancel', SubmitType::class, [
                'label' => $this->trans('Cancel'),
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
