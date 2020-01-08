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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;

/**
 * Configuration form type class.
 */
class ConfigType extends AbstractType
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
        $builder
            ->add('itemsperpage', IntegerType::class, [
                'label' => $this->trans('Items per page'),
                'attr' => [
                    'maxlength' => 3,
                    'min' => 1
                ]
            ])
            ->add('defaultgroup', ChoiceType::class, [
                'label' => $this->trans('Initial user group'),
                'choices' => $options['groups'],
            ])
            ->add('hideclosed', CheckboxType::class, [
                'label' => $this->trans('Hide closed groups'),
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false
            ])
            ->add('hidePrivate', CheckboxType::class, [
                'label' => $this->trans('Hide private groups'),
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false
            ])
            ->add('mailwarning', CheckboxType::class, [
                'label' => $this->trans('Receive e-mail alert when there are new applicants'),
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false
            ])
            ->add('save', SubmitType::class, [
                'label' => $this->trans('Save'),
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
            ->add('cancel', SubmitType::class, [
                'label' => $this->trans('Cancel'),
                'icon' => 'fa-times',
                'attr' => [
                    'class' => 'btn btn-default'
                ]
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulagroupsmodule_config';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'groups' => []
        ]);
    }
}
