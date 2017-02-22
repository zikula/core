<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ExtensionsModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Extension modification form type.
 */
class ExtensionModifyType extends AbstractType
{
    /**
* @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translator = $options['translator'];

        $builder
            ->add('id', 'Symfony\Component\Form\Extension\Core\Type\HiddenType')
            ->add('displayname', 'Symfony\Component\Form\Extension\Core\Type\TextType')
            ->add('url', 'Symfony\Component\Form\Extension\Core\Type\TextType')
            ->add('description', 'Symfony\Component\Form\Extension\Core\Type\TextType')
            ->add('save', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $translator->__('Save'),
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
            ->add('defaults', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $translator->__('Reload Defaults'),
                'icon' => 'fa-refresh',
                'attr' => [
                    'class' => 'btn btn-warning'
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
* @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'zikulaextensionsmodule_extensionmodify';
    }

    /**
* @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Zikula\ExtensionsModule\Entity\ExtensionEntity',
            'translator' => null
        ]);
    }
}
