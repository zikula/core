<?php

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
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;

class SearchType extends AbstractType
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
            ->setMethod('GET')
            ->add('q', SearchInputType::class, [
                'label' => $this->__('Search keywords'),
                'attr' => [
                    'maxlength' => 255,
                    'min' => 1,
                    'autosave' => 'Search',
                    'results' => '10'
                ],
                'required' => false,
                'constraints' => [new NotBlank(['message' => $this->__('Error! You did not enter any keywords to search for.')])]
            ])
            ->add('searchType', ChoiceType::class, [
                'label' => $this->__('Keyword settings'),
                'choices' => [
                    $this->__('All Words') => 'AND',
                    $this->__('Any Words') => 'OR',
                    $this->__('Exact phrase') => 'EXACT',
                ]
            ])
            ->add('searchOrder', ChoiceType::class, [
                'label' => $this->__('Order of results'),
                'choices' => [
                    $this->__('Newest first') => 'newest',
                    $this->__('Oldest first') => 'oldest',
                    $this->__('Alphabetical') => 'alphabetical',
                ]
            ])
            ->add('search', SubmitType::class, [
                'label' => $this->__('Search now'),
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
            'csrf_protection' => false
        ]);
    }
}
