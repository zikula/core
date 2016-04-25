<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\BlocksModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Zikula\BlocksModule\Api\BlockFilterApi;

class BlockFilterType extends AbstractType
{
    /**
     * @var BlockFilterApi
     */
    private $blockFilterApi;

    /**
     * BlockFilterType constructor.
     * @param $blockFilterApi
     */
    public function __construct(BlockFilterApi $blockFilterApi)
    {
        $this->blockFilterApi = $blockFilterApi;
    }

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

    public function getBlockPrefix()
    {
        return 'zikulablocksmodule_blockfilter';
    }
}
