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

namespace Zikula\UsersModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class MailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('userIds', HiddenType::class)
            ->add('from', TextType::class, [
                'label' => 'Sender name',
            ])
            ->add('replyto', EmailType::class, [
                'label' => 'Replyto email address',
            ])
            ->add('subject', TextType::class, [
                'label' => 'Subject',
            ])
            ->add('format', ChoiceType::class, [
                'choices' => [
                    'Text' => 'text',
                    'Html' => 'html',
                ],
                'label' => 'Format',
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message',
            ])
            ->add('batchsize', IntegerType::class, [
                'label' => 'Send mail in batches of',
            ])
            ->add('send', SubmitType::class, [
                'label' => 'Send mail',
                'icon' => 'fa-angle-double-right',
                'attr' => [
                    'class' => 'btn-success'
                ]
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulausersmodule_mail';
    }
}
