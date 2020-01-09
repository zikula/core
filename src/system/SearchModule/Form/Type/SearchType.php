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
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;

class SearchType extends AbstractType
{
    use TranslatorTrait;

    public function __construct(TranslatorInterface $translator)
    {
        $this->setTranslator($translator);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod('GET')
            ->add('q', SearchInputType::class, [
                'label' => $this->trans('Search keywords'),
                'attr' => [
                    'maxlength' => 255,
                    'min' => 1,
                    'autosave' => 'Search',
                    'results' => '10'
                ],
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => $this->trans('Error! You did not enter any keywords to search for.')
                    ])
                ]
            ])
            ->add('searchType', ChoiceType::class, [
                'label' => $this->trans('Keyword settings'),
                'choices' => [
                    $this->trans('All Words') => 'AND',
                    $this->trans('Any Words') => 'OR',
                    $this->trans('Exact phrase') => 'EXACT'
                ]
            ])
            ->add('searchOrder', ChoiceType::class, [
                'label' => $this->trans('Order of results'),
                'choices' => [
                    $this->trans('Newest first') => 'newest',
                    $this->trans('Oldest first') => 'oldest',
                    $this->trans('Alphabetical') => 'alphabetical'
                ]
            ])
            ->add('search', SubmitType::class, [
                'label' => $this->trans('Search now'),
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
