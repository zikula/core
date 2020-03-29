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
                    'Development/debug mode (Do not send any email)' => 'test'/*'null'*/
                ]
            ])
            ->add('mailer_id', TextType::class, [
                'label' => 'Mailer ID',
                'attr' => [
                    'maxlength' => 50
                ],
                'help' => 'The ACCESS_KEY, USERNAME, ID or apikey for the selected transport.',
                'required' => false
            ])
            ->add('mailer_key', TextType::class, [
                'label' => 'Mailer Key',
                'attr' => [
                    'maxlength' => 50
                ],
                'help' => 'The SECRET_KEY, PASSWORD, ID or KEY for the selected transport.',
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
                'help' => 'Use query parameters syntax, for example: <code>?param1=value1&amp;param2=value2</code>',
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
