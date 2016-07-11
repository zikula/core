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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\BlocksModule\Api\BlockApi;
use Zikula\BlocksModule\Api\BlockFilterApi;
use Zikula\Bundle\FormExtensionBundle\Form\DataTransformer\NullToEmptyTransformer;

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
     * @var
     */
    private $translator;

    /**
     * BlockType constructor.
     * @param BlockApi $blockApi
     * @param BlockFilterApi $blockFilterApi
     * @param $translator
     */
    public function __construct(BlockApi $blockApi, BlockFilterApi $blockFilterApi, $translator)
    {
        $this->blockApi = $blockApi;
        $this->blockFilterApi = $blockFilterApi;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('bid', 'Symfony\Component\Form\Extension\Core\Type\HiddenType')
            ->add('bkey', 'Symfony\Component\Form\Extension\Core\Type\HiddenType')
            ->add('blocktype', 'Symfony\Component\Form\Extension\Core\Type\HiddenType')
            ->add($builder->create('title', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'required' => false
            ])->addModelTransformer(new NullToEmptyTransformer()))
            ->add($builder->create('description', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'required' => false
            ])->addModelTransformer(new NullToEmptyTransformer()))
            ->add($builder->create('language', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'choices' => \ZLanguage::getInstalledLanguageNames(),
                'required' => false,
                'placeholder' => $this->translator->__('All')
            ])->addModelTransformer(new NullToEmptyTransformer()))
            ->add('positions', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', [
                'class' => 'Zikula\BlocksModule\Entity\BlockPositionEntity',
                'choice_label' => 'name',
                'multiple' => true,
                'required' => false,
            ])
            ->add('filters', 'Symfony\Component\Form\Extension\Core\Type\CollectionType', [
                'entry_type' => 'Zikula\BlocksModule\Form\Type\BlockFilterType',
                'allow_add' => true,
                'allow_delete' => true,
                'label' => 'Custom filters',
                'required' => false
            ])
            ->add('save', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $this->translator->__('Save'),
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
            ->add('cancel', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
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
