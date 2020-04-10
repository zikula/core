<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
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
use Translation\Extractor\Annotation\Ignore;
use Translation\Extractor\Annotation\Translate;

/**
 * Application management form type class.
 */
class ManageApplicationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $reason = 'accept' === $options['data']['theAction']
            ? /** @Translate */'Congratulations! Your group application has been accepted. You have been granted all the privileges assigned to the group of which you are now member.'
            : /** @Translate */'Sorry! This is a message to inform you with regret that your application for membership of the requested private group has been rejected.'
        ;
        $builder
            ->add('theAction', HiddenType::class)
            ->add('application', HiddenType::class, [
                'property_path' => '[application].app_id'
            ])
            ->add('reason', TextareaType::class, [
                'label' => 'Email content',
                'data' => $reason,
                'required' => false
            ])
            ->add('sendtag', ChoiceType::class, [
                'label' => 'Notification type',
                'label_attr' => ['class' => 'radio-custom'],
                'data' => 1,
                'choices' => [
                    'None' => 0,
                    'E-mail' => 1
                ],
                'expanded' => true
            ])
            ->add('save', SubmitType::class, [
                /** @Ignore */
                'label' => 'deny' === $options['data']['theAction'] ? /** @Translate */'Deny' : /** @Translate */'Accept',
                'icon' => 'deny' === $options['data']['theAction'] ? 'fa-user-times' : 'fa-user-plus',
                'attr' => [
                    'class' => 'btn-success'
                ]
            ])
            ->add('cancel', SubmitType::class, [
                'label' => 'Cancel',
                'icon' => 'fa-times'
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulagroupsmodule_manageapplication';
    }
}
