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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Translation\Extractor\Annotation\Ignore;
use Zikula\BlocksModule\Api\ApiInterface\BlockFilterApiInterface;

class BlockFilterType extends AbstractType
{
    /**
     * @var BlockFilterApiInterface
     */
    private $blockFilterApi;

    public function __construct(BlockFilterApiInterface $blockFilterApi)
    {
        $this->blockFilterApi = $blockFilterApi;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('attribute', ChoiceType::class, [
                'label' => 'Attribute',
                'choices' => /** @Ignore */$this->blockFilterApi->getFilterAttributeChoices()
            ])
            ->add('queryParameter', TextType::class, [
                'label' => 'Query parameter',
                'required' => false
            ])
            ->add('comparator', ChoiceType::class, [
                'label' => 'Comparator',
                'choices' => [
                    '==' => '==',
                    '!=' => '!=',
                    '>=' => '>=',
                    '<=' => '<=',
                    '>' => '>',
                    '<' => '<',
                    'in_array' => 'in_array',
                    '!in_array' => '!in_array'
                ]
            ])
            ->add('value', TextType::class, [
                'label' => 'Value',
                'required' => false
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulablocksmodule_blockfilter';
    }
}
