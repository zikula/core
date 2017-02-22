<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MailType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('userIds', 'Symfony\Component\Form\Extension\Core\Type\HiddenType')
            ->add('from', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $options['translator']->__('Sender name'),
            ])
            ->add('replyto', 'Symfony\Component\Form\Extension\Core\Type\EmailType', [
                'label' => $options['translator']->__('Replyto email address'),
            ])
            ->add('subject', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $options['translator']->__('Subject'),
            ])
            ->add('format', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'choices' => [
                    $options['translator']->__('Text') => 'text',
                    $options['translator']->__('Html') => 'html',
                ],
                'choices_as_values' => true,
                'label' => $options['translator']->__('Format'),
            ])
            ->add('message', 'Symfony\Component\Form\Extension\Core\Type\TextareaType', [
                'label' => $options['translator']->__('Message'),
            ])
            ->add('batchsize', 'Symfony\Component\Form\Extension\Core\Type\IntegerType', [
                'label' => $options['translator']->__('Send mail in batches of'),
            ])
            ->add('send', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $options['translator']->__('Send Mail'),
                'icon' => 'fa-angle-double-right',
                'attr' => ['class' => 'btn btn-success'],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikulausersmodule_mail';
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
