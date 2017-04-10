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
use Zikula\BlocksModule\Api\ApiInterface\BlockFilterApiInterface;

class BlockFilterType extends AbstractType
{
    /**
     * @var BlockFilterApiInterface
     */
    private $blockFilterApi;

    /**
     * BlockFilterType constructor.
     *
     * @param BlockFilterApiInterface $blockFilterApi
     */
    public function __construct(BlockFilterApiInterface $blockFilterApi)
    {
        $this->blockFilterApi = $blockFilterApi;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('attribute', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'choices' => $this->blockFilterApi->getFilterAttributeChoices(),
                'choices_as_values' => true,
            ])
            ->add('queryParameter', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'required' => false
            ])
            ->add('comparator', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'choices' => [
                    '==' => '==',
                    '!=' => '!=',
                    '>=' => '>=',
                    '<=' => '<=',
                    '>' => '>',
                    '<' => '<',
                    'in_array' => 'in_array',
                    '!in_array' => '!in_array'
                ],
                'choices_as_values' => true,
            ])
            ->add('value', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'required' => false
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikulablocksmodule_blockfilter';
    }
}
