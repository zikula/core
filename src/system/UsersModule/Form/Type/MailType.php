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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('userIds', HiddenType::class)
            ->add('from', TextType::class, [
                'label' => $options['translator']->__('Sender name'),
            ])
            ->add('replyto', EmailType::class, [
                'label' => $options['translator']->__('Replyto email address'),
            ])
            ->add('subject', TextType::class, [
                'label' => $options['translator']->__('Subject'),
            ])
            ->add('format', ChoiceType::class, [
                'choices' => [
                    $options['translator']->__('Text') => 'text',
                    $options['translator']->__('Html') => 'html',
                ],
                'label' => $options['translator']->__('Format'),
            ])
            ->add('message', TextareaType::class, [
                'label' => $options['translator']->__('Message'),
            ])
            ->add('batchsize', IntegerType::class, [
                'label' => $options['translator']->__('Send mail in batches of'),
            ])
            ->add('send', SubmitType::class, [
                'label' => $options['translator']->__('Send Mail'),
                'icon' => 'fa-angle-double-right',
                'attr' => ['class' => 'btn btn-success'],
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulausersmodule_mail';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => null,
        ]);
    }
}
