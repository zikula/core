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
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;

/**
 * Configuration form type class.
 */
class TestType extends AbstractType
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
            ->add('fromName', TextType::class, [
                'label' => $this->__('Sender\'s name'),
                'disabled' => true
            ])
            ->add('fromAddress', EmailType::class, [
                'label' => $this->__('Sender\'s e-mail address'),
                'disabled' => true
            ])
            ->add('toName', TextType::class, [
                'label' => $this->__('Recipient\'s name'),
                'attr' => [
                    'maxlength' => 50
                ],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('toAddress', EmailType::class, [
                'label' => $this->__('Recipient\'s e-mail address'),
                'attr' => [
                    'maxlength' => 50
                ],
                'constraints' => [
                    new NotBlank(),
                    new Email()
                ]
            ])
            ->add('subject', TextType::class, [
                'label' => $this->__('Subject'),
                'attr' => [
                    'maxlength' => 50
                ],
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('messageType', ChoiceType::class, [
                'label' => $this->__('Message type'),
                'empty_data' => 'text',
                'choices' => [
                    'Plain-text message' => 'text',
                    'HTML-formatted message' => 'html',
                    'Multi-part message' => 'multipart'
                ],
                'expanded' => false
            ])
            ->add('bodyHtml', TextareaType::class, [
                'label' => $this->__('HTML-formatted message'),
                'required' => false
            ])
            ->add('bodyText', TextareaType::class, [
                'label' => $this->__('Plain-text message'),
                'required' => false
            ])
            ->add('test', SubmitType::class, [
                'label' => $this->__('Send test email'),
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

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikulamailermodule_test';
    }
}
