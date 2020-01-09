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
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;

class MailType extends AbstractType
{
    use TranslatorTrait;

    public function __construct(TranslatorInterface $translator)
    {
        $this->setTranslator($translator);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('userIds', HiddenType::class)
            ->add('from', TextType::class, [
                'label' => $this->trans('Sender name'),
            ])
            ->add('replyto', EmailType::class, [
                'label' => $this->trans('Replyto email address'),
            ])
            ->add('subject', TextType::class, [
                'label' => $this->trans('Subject'),
            ])
            ->add('format', ChoiceType::class, [
                'choices' => [
                    $this->trans('Text') => 'text',
                    $this->trans('Html') => 'html',
                ],
                'label' => $this->trans('Format'),
            ])
            ->add('message', TextareaType::class, [
                'label' => $this->trans('Message'),
            ])
            ->add('batchsize', IntegerType::class, [
                'label' => $this->trans('Send mail in batches of'),
            ])
            ->add('send', SubmitType::class, [
                'label' => $this->trans('Send Mail'),
                'icon' => 'fa-angle-double-right',
                'attr' => ['class' => 'btn btn-success']
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulausersmodule_mail';
    }
}
