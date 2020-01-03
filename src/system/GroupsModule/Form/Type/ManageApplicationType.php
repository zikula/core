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

namespace Zikula\GroupsModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;

/**
 * Application management form type class.
 */
class ManageApplicationType extends AbstractType
{
    use TranslatorTrait;

    public function __construct(TranslatorInterface $translator)
    {
        $this->setTranslator($translator);
    }

    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $reason = 'accept' === $options['data']['theAction']
            ? $this->__('Congratulations! Your group application has been accepted. You have been granted all the privileges assigned to the group of which you are now member.')
            : $this->__('Sorry! This is a message to inform you with regret that your application for membership of the requested private group has been rejected.');
        $builder
            ->add('theAction', HiddenType::class)
            ->add('application', HiddenType::class, [
                'property_path' => '[application].app_id'
            ])
            ->add('reason', TextareaType::class, [
                'label' => $this->__('Email content'),
                'data' => $reason,
                'required' => false
            ])
            ->add('sendtag', ChoiceType::class, [
                'label' => $this->__('Notification type'),
                'label_attr' => ['class' => 'radio-custom'],
                'data' => 1,
                'choices' => [
                    $this->__('None') => 0,
                    $this->__('E-mail') => 1
                ],
                'expanded' => true
            ])
            ->add('save', SubmitType::class, [
                'label' => 'deny' === $options['data']['theAction'] ? $this->__('Deny') : $this->__('Accept'),
                'icon' => 'deny' === $options['data']['theAction'] ? 'fa-user-times' : 'fa-user-plus',
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

    public function getBlockPrefix()
    {
        return 'zikulagroupsmodule_manageapplication';
    }
}
