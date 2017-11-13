<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SearchModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SearchType as SearchInputType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class SearchType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translator = $options['translator'];

        $builder
            ->setMethod('GET')
            ->add('q', SearchInputType::class, [
                'label' => $translator->__('Search keywords'),
                'attr' => [
                    'maxlength' => 255,
                    'min' => 1,
                    'autosave' => 'Search',
                    'results' => '10'
                ],
                'required' => false,
                'constraints' => [new NotBlank(['message' => $translator->__('Error! You did not enter any keywords to search for.')])]
            ])
            ->add('searchType', ChoiceType::class, [
                'label' => $translator->__('Keyword settings'),
                'choices' => [
                    $translator->__('All Words') => 'AND',
                    $translator->__('Any Words') => 'OR',
                    $translator->__('Exact phrase') => 'EXACT',
                ],
                'choices_as_values' => true,
            ])
            ->add('searchOrder', ChoiceType::class, [
                'label' => $translator->__('Order of results'),
                'choices' => [
                    $translator->__('Newest first') => 'newest',
                    $translator->__('Oldest first') => 'oldest',
                    $translator->__('Alphabetical') => 'alphabetical',
                ],
                'choices_as_values' => true,
            ])
            ->add('search', SubmitType::class, [
                'label' => $translator->__('Search now'),
                'icon' => 'fa-search',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikulasearchmodule_search';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'translator' => null
        ]);
    }
}
