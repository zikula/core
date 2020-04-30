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

namespace Zikula\BlocksModule\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Translation\Extractor\Annotation\Ignore;
use Zikula\BlocksModule\Entity\BlockEntity;
use Zikula\BlocksModule\Entity\BlockPositionEntity;
use Zikula\Bundle\FormExtensionBundle\Form\DataTransformer\NullToEmptyTransformer;
use Zikula\SettingsModule\Api\ApiInterface\LocaleApiInterface;

class BlockType extends AbstractType
{
    /**
     * @var LocaleApiInterface
     */
    private $localeApi;

    public function __construct(LocaleApiInterface $localeApi)
    {
        $this->localeApi = $localeApi;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('bid', HiddenType::class)
            ->add('bkey', HiddenType::class)
            ->add('blocktype', HiddenType::class)
            ->add($builder->create('title', TextType::class, [
                'label' => 'Title',
                'required' => false
            ])->addModelTransformer(new NullToEmptyTransformer()))
            ->add($builder->create('description', TextType::class, [
                'label' => 'Description',
                'required' => false
            ])->addModelTransformer(new NullToEmptyTransformer()))
            ->add($builder->create('language', ChoiceType::class, [
                'label' => 'Language',
                'choices' => /** @Ignore */$this->localeApi->getSupportedLocaleNames(null, $options['locale']),
                'required' => false,
                'placeholder' => 'All'
            ])->addModelTransformer(new NullToEmptyTransformer()))
            ->add('positions', EntityType::class, [
                'label' => 'Positions',
                'class' => BlockPositionEntity::class,
                'choice_label' => 'name',
                'multiple' => true,
                'required' => false,
            ])
            ->add('filters', CollectionType::class, [
                'label' => 'Custom filters',
                'entry_type' => BlockFilterType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save',
                'icon' => 'fa-check',
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
        return 'zikulablocksmodule_block';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BlockEntity::class,
            'locale' => 'en'
        ]);
    }
}
