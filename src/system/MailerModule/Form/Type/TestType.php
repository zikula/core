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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Configuration form type class.
 */
class TestType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translator = $options['translator'];

        $builder
            ->add('fromName', TextType::class, [
                'label' => $translator->__('Sender\'s name'),
                'disabled' => true
            ])
            ->add('fromAddress', EmailType::class, [
                'label' => $translator->__('Sender\'s e-mail address'),
                'disabled' => true
            ])
            ->add('toName', TextType::class, [
                'label' => $translator->__('Recipient\'s name'),
                'attr' => [
                    'maxlength' => 50
                ],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('toAddress', EmailType::class, [
                'label' => $translator->__('Recipient\'s e-mail address'),
                'attr' => [
                    'maxlength' => 50
                ],
                'constraints' => [
                    new NotBlank(),
                    new Email()
                ]
            ])
            ->add('subject', TextType::class, [
                'label' => $translator->__('Subject'),
                'attr' => [
                    'maxlength' => 50
                ],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('messageType', ChoiceType::class, [
                'label' => $translator->__('Message type'),
                'empty_data' => 'text',
                'choices' => [
                    'Plain-text message' => 'text',
                    'HTML-formatted message' => 'html',
                    'Multi-part message' => 'multipart'
                ],
                'choices_as_values' => true,
                'expanded' => false
            ])
            ->add('bodyHtml', TextareaType::class, [
                'label' => $translator->__('HTML-formatted message'),
                'required' => false
            ])
            ->add('bodyText', TextareaType::class, [
                'label' => $translator->__('Plain-text message'),
                'required' => false
            ])
            ->add('test', SubmitType::class, [
                'label' => $translator->__('Send test email'),
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
        return 'zikulamailermodule_test';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => null
        ]);
    }
}
