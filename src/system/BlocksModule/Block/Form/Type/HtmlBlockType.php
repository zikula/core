<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BlocksModule\Block\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class HtmlBlockType
 */
class HtmlBlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content', 'Symfony\Component\Form\Extension\Core\Type\TextareaType', [
                'constraints' => [
                    new NotBlank(),
                ]
            ])
        ;
    }

    public function getName()
    {
        return 'zikulablocksmodule_htmlblock';
    }
}
