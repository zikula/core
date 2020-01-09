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

namespace Zikula\ExtensionsModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;

/**
 * Extension modification form type.
 */
class ExtensionModifyType extends AbstractType
{
    use TranslatorTrait;

    public function __construct(TranslatorInterface $translator)
    {
        $this->setTranslator($translator);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', HiddenType::class)
            ->add('displayname', TextType::class, [
                'label' => $this->trans('Display name')
            ])
            ->add('url', TextType::class, [
                'label' => $this->trans('URL'),
                'input_group' => ['left' => '/'],
                'help' => $this->trans('WARNING: changing the url affects SEO by breaking existing indexed search results.')
            ])
            ->add('description', TextType::class, [
                'label' => $this->trans('Description')
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
            ->add('defaults', SubmitType::class, [
                'label' => $this->trans('Reload Defaults'),
                'icon' => 'fa-sync',
                'attr' => [
                    'class' => 'btn btn-default'
                ]
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulaextensionsmodule_extensionmodify';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ExtensionEntity::class
        ]);
    }
}
