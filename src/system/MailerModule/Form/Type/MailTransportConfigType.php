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

namespace Zikula\MailerModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Configuration form type class.
 */
class MailTransportConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('transport', ChoiceType::class, [
                'label' => 'Mailer transport',
                'choices' => [
                    'Amazon SES' => 'amazon',
                    'Google Gmail' => 'gmail',
                    'Mailchimp Mandrill' => 'mailchimp',
                    'Mailgun' => 'mailgun',
                    'Postmark' => 'postmark',
                    'Sendgrid' => 'sendgrid',
                    'SMTP server' => 'smtp',
                    'Sendmail binary' => 'sendmail',
                    'Development/debug mode (Do not send any email)' => 'test'/* 'null' */
                ],
                'help' => 'How mails are sent and delivered. For example, to use a mail account, choose "SMTP server". Alternatively, instead of using your own SMTP server, you can send emails via a 3rd party provider. For further information about the mailer transport setup please refer to the Symfony docs %link%. Please do not install any mailer transport using composer, as they are already included in Zikula.',
                'help_translation_parameters' => [
                    '%link%' => '<a href="https://symfony.com/doc/current/mailer.html#transport-setup" target="_blank">here</a>',
                ],
                'help_html' => true
            ])
            ->add('mailer_id', TextType::class, [
                'label' => 'Authentication identifier',
                'attr' => [
                    'maxlength' => 50
                ],
                'help' => 'The user name, access key or API key for the selected transport. Defines which account should be used for sending.',
                'required' => false
            ])
            ->add('mailer_key', TextType::class, [
                'label' => 'Authentication secret',
                'attr' => [
                    'maxlength' => 50
                ],
                'help' => 'The password or secret key for the selected transport. Used for verifying authentication. Not saved to the database, only written to the <code>.env.local</code> file.',
                'help_html' => true,
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
            ->add('customParameters', TextType::class, [
                'label' => 'Custom parameters',
                'attr' => [
                    'maxlength' => 255
                ],
                'required' => false,
                'help' => 'Use query parameters syntax, for example: <code>?param1=value1&amp;param2=value2</code>.',
                'help_html' => true
            ])
            ->add('enableLogging', CheckboxType::class, [
                'label' => 'Enable logging of sent mail',
                'label_attr' => ['class' => 'switch-custom'],
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
}
