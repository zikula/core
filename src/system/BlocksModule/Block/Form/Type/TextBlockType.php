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

namespace Zikula\BlocksModule\Block\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class TextBlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content', TextareaType::class, [
                'constraints' => [
                    new NotBlank()
                ]
            ])
        ;
        $builder->get('content')
            ->addModelTransformer(new CallbackTransformer(
                static function ($originalDescription) {
                    return $originalDescription;
                },
                static function ($submittedDescription) {
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
