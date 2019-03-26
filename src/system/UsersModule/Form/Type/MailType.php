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
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;

class MailType extends AbstractType
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
            ->add('userIds', HiddenType::class)
            ->add('from', TextType::class, [
                'label' => $this->__('Sender name'),
            ])
            ->add('replyto', EmailType::class, [
                'label' => $this->__('Replyto email address'),
            ])
            ->add('subject', TextType::class, [
                'label' => $this->__('Subject'),
            ])
            ->add('format', ChoiceType::class, [
                'choices' => [
                    $this->__('Text') => 'text',
                    $this->__('Html') => 'html',
                ],
                'label' => $this->__('Format'),
            ])
            ->add('message', TextareaType::class, [
                'label' => $this->__('Message'),
            ])
            ->add('batchsize', IntegerType::class, [
                'label' => $this->__('Send mail in batches of'),
            ])
            ->add('send', SubmitType::class, [
                'label' => $this->__('Send Mail'),
                'icon' => 'fa-angle-double-right',
                'attr' => ['class' => 'btn btn-success']
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
}
