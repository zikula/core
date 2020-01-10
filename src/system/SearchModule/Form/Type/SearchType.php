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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod('GET')
            ->add('q', SearchInputType::class, [
                'label' => 'Search keywords',
                'attr' => [
                    'maxlength' => 255,
                    'min' => 1,
                    'autosave' => 'Search',
                    'results' => '10'
                ],
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Error! You did not enter any keywords to search for.'
                    ])
                ]
            ])
            ->add('searchType', ChoiceType::class, [
                'label' => 'Keyword settings',
                'choices' => [
                    'All words' => 'AND',
                    'Any words' => 'OR',
                    'Exact phrase' => 'EXACT'
                ]
            ])
            ->add('searchOrder', ChoiceType::class, [
                'label' => 'Order of results',
                'choices' => [
                    'Newest first' => 'newest',
                    'Oldest first' => 'oldest',
                    'Alphabetical' => 'alphabetical'
                ]
            ])
            ->add('search', SubmitType::class, [
                'label' => 'Search now',
                'icon' => 'fa-search',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulasearchmodule_search';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false
        ]);
    }
}
