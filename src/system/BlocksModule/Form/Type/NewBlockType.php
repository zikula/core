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
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Translation\Extractor\Annotation\Ignore;
use Zikula\BlocksModule\Api\ApiInterface\BlockApiInterface;

class NewBlockType extends AbstractType
{
    /**
     * @var BlockApiInterface
     */
    private $blockApi;

    public function __construct(BlockApiInterface $blockApi)
    {
        $this->blockApi = $blockApi;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('bkey', ChoiceType::class, [
                'label' => 'Block type',
                'placeholder' => 'Choose a block type',
                'choices' => /** @Ignore */array_flip($this->blockApi->getAvailableBlockTypes())
            ])
            ->add('choose', SubmitType::class, [
                'label' => 'Choose',
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulablocksmodule_newblock';
    }
}
