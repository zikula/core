<?php
/**
 * Copyright Pages Team 2015
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Pages
 * @link https://github.com/zikula-modules/Pages
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\BlocksModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\BlocksModule\Api\BlockApi;

class AdminViewFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sort-field', 'hidden')
            ->add('sort-direction', 'hidden')
            ->add('position', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'choices' => $options['positionChoices'],
                'required' => false,
                'placeholder' => $options['translator']->__('All'),
                'attr' => ['class' => 'input-sm']
            ])
            ->add('module', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'choices' => $options['moduleChoices'],
                'required' => false,
                'placeholder' => $options['translator']->__('All'),
                'attr' => ['class' => 'input-sm']
            ])
            ->add('language', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'choices' => \ZLanguage::getInstalledLanguageNames(),
                'required' => false,
                'placeholder' => $options['translator']->__('All'),
                'attr' => ['class' => 'input-sm']
            ])
            ->add('active', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'choices' => [
                    BlockApi::BLOCK_ACTIVE => $options['translator']->__('Active'),
                    BlockApi::BLOCK_INACTIVE => $options['translator']->__('Inactive'),
                ],
                'required' => false,
                'placeholder' => $options['translator']->__('All'),
                'attr' => ['class' => 'input-sm']
            ])
            ->add('filterButton', 'submit', [
                'icon' => 'fa-filter fa-lg',
                'label' => __('Filter'),
                'attr' => ['class' => "btn btn-default btn-sm"]
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulablocksmodule_adminviewfilter';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => [
                'class' => 'form form-inline',
            ],
            'translator' => null,
            'moduleChoices' => [],
            'positionChoices' => []
        ]);
    }
}
