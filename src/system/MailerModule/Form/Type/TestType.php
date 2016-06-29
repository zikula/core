<?php
/**
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
            ->add('fromName', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $translator->__('Sender\'s name'),
                'disabled' => true
            ])
            ->add('fromAddress', 'Symfony\Component\Form\Extension\Core\Type\EmailType', [
                'label' => $translator->__('Sender\'s e-mail address'),
                'disabled' => true
            ])
            ->add('toName', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $translator->__('Recipient\'s name'),
                'constraints' => [
                    new NotBlank()
                ],
                'max_length' => 50
            ])
            ->add('toAddress', 'Symfony\Component\Form\Extension\Core\Type\EmailType', [
                'label' => $translator->__('Recipient\'s e-mail address'),
                'constraints' => [
                    new NotBlank(),
                    new Email()
                ],
                'max_length' => 50
            ])
            ->add('subject', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $translator->__('Subject'),
                'constraints' => [
                    new NotBlank()
                ],
                'max_length' => 50
            ])
            ->add('messageType', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
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
            ->add('bodyHtml', 'Symfony\Component\Form\Extension\Core\Type\TextareaType', [
                'label' => $translator->__('HTML-formatted message'),
                'required' => false
            ])
            ->add('bodyText', 'Symfony\Component\Form\Extension\Core\Type\TextareaType', [
                'label' => $translator->__('Plain-text message'),
                'required' => false
            ])
            ->add('test', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $translator->__('Send test email'),
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
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikulamailermodule_test';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
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
