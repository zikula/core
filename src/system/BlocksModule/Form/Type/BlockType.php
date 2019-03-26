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
use Zikula\BlocksModule\Entity\BlockEntity;
use Zikula\BlocksModule\Entity\BlockPositionEntity;
use Zikula\Bundle\FormExtensionBundle\Form\DataTransformer\NullToEmptyTransformer;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\SettingsModule\Api\ApiInterface\LocaleApiInterface;

class BlockType extends AbstractType
{
    use TranslatorTrait;

    /**
     * @var LocaleApiInterface
     */
    private $localeApi;

    /**
     * BlockType constructor.
     *
     * @param TranslatorInterface $translator
     * @param LocaleApiInterface $localeApi
     */
    public function __construct(
        TranslatorInterface $translator,
        LocaleApiInterface $localeApi
    ) {
        $this->setTranslator($translator);
        $this->localeApi = $localeApi;
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
                'choices' => $this->localeApi->getSupportedLocaleNames(null, $options['locale']),
                'required' => false,
                'placeholder' => $this->__('All')
            ])->addModelTransformer(new NullToEmptyTransformer()))
            ->add('positions', EntityType::class, [
                'class' => BlockPositionEntity::class,
                'choice_label' => 'name',
                'multiple' => true,
                'required' => false,
            ])
            ->add('filters', CollectionType::class, [
                'entry_type' => BlockFilterType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'label' => $this->__('Custom filters'),
                'required' => false
            ])
            ->add('save', SubmitType::class, [
                'label' => $this->__('Save'),
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
            ->add('cancel', SubmitType::class, [
                'label' => $this->__('Cancel'),
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
            'data_class' => BlockEntity::class,
            'locale' => 'en'
        ]);
    }
}
