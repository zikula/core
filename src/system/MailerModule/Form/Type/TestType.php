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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Mailer testing form type class.
 */
class TestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fromName', TextType::class, [
                'label' => 'Sender\'s name',
                'disabled' => true
            ])
            ->add('fromAddress', EmailType::class, [
                'label' => 'Sender\'s e-mail address',
                'disabled' => true
            ])
            ->add('toName', TextType::class, [
                'label' => 'Recipient\'s name',
                'attr' => [
                    'maxlength' => 50
                ],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('toAddress', EmailType::class, [
                'label' => 'Recipient\'s e-mail address',
                'attr' => [
                    'maxlength' => 50
                ],
                'constraints' => [
                    new NotBlank(),
                    new Email()
                ]
            ])
            ->add('subject', TextType::class, [
                'label' => 'Subject',
                'attr' => [
                    'maxlength' => 50
                ],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('messageType', ChoiceType::class, [
                'label' => 'Message type',
                'empty_data' => 'text',
                'choices' => [
                    'Plain-text message' => 'text',
                    'HTML-formatted message' => 'html',
                    'Multi-part message' => 'multipart'
                ],
                'expanded' => false
            ])
            ->add('bodyHtml', TextareaType::class, [
                'label' => 'HTML-formatted message',
                'required' => false
            ])
            ->add('bodyText', TextareaType::class, [
                'label' => 'Plain-text message',
                'required' => false
            ])
            ->add('test', SubmitType::class, [
                'label' => 'Send test email',
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
            ->add('cancel', SubmitType::class, [
                'label' => 'Cancel',
                'icon' => 'fa-times',
                'attr' => [
                    'class' => 'btn btn-default'
                ]
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulamailermodule_test';
    }
}
