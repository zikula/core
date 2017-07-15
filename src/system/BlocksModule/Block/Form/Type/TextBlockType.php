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
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class TextBlockType
 */
class TextBlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content', TextareaType::class, [
                'constraints' => [
                    new NotBlank(),
                ]
            ])
        ;
        $builder->get('content')
            ->addModelTransformer(new CallbackTransformer(
                function($originalDescription) {
                    return $originalDescription;
                },
                function($submittedDescription) {
                    // remove all HTML tags
                    return strip_tags($submittedDescription);
                }
            ))
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulablocksmodule_textblock';
    }
}
