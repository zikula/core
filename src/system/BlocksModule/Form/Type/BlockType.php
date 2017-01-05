<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
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
use Zikula\BlocksModule\Api\BlockApi;
use Zikula\BlocksModule\Api\BlockFilterApi;
use Zikula\Bundle\FormExtensionBundle\Form\DataTransformer\NullToEmptyTransformer;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\SettingsModule\Api\LocaleApi;

class BlockType extends AbstractType
{
    /**
     * @var BlockApi
     */
    private $blockApi;

    /**
     * @var BlockFilterApi
     */
    private $blockFilterApi;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var LocaleApi
     */
    private $localeApi;

    /**
     * BlockType constructor.
     * @param BlockApi $blockApi
     * @param BlockFilterApi $blockFilterApi
     * @param TranslatorInterface $translator
     * @param LocaleApi $localeApi
     */
    public function __construct(BlockApi $blockApi, BlockFilterApi $blockFilterApi, TranslatorInterface $translator, LocaleApi $localeApi)
    {
        $this->blockApi = $blockApi;
        $this->blockFilterApi = $blockFilterApi;
        $this->translator = $translator;
        $this->localeApi = $localeApi;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('bid', HiddenType::class)
            ->add('bkey', HiddenType::class)
            ->add('blocktype', HiddenType::class)
            ->add($builder->create('title', TextType::class, [
                'required' => false
            ])->addModelTransformer(new NullToEmptyTransformer()))
            ->add($builder->create('description', TextType::class, [
                'required' => false
            ])->addModelTransformer(new NullToEmptyTransformer()))
            ->add($builder->create('language', ChoiceType::class, [
                'choices' => $this->localeApi->getSupportedLocaleNames(),
                'required' => false,
                'placeholder' => $this->translator->__('All')
            ])->addModelTransformer(new NullToEmptyTransformer()))
            ->add('positions', EntityType::class, [
                'class' => 'Zikula\BlocksModule\Entity\BlockPositionEntity',
                'choice_label' => 'name',
                'multiple' => true,
                'required' => false,
            ])
            ->add('filters', CollectionType::class, [
                'entry_type' => BlockFilterType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'label' => 'Custom filters',
                'required' => false
            ])
            ->add('save', SubmitType::class, [
                'label' => $this->translator->__('Save'),
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
            ->add('cancel', SubmitType::class, [
                'label' => $this->translator->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => [
                    'class' => 'btn btn-default'
                ]
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikulablocksmodule_block';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Zikula\BlocksModule\Entity\BlockEntity',
        ]);
    }
}
