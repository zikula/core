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

namespace Zikula\BlocksModule\Block\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class TextBlockType
 * @package Zikula\BlocksModule\Block\Form\Type
 */
class TextBlockType extends AbstractType
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
        $builder->get('content')
            ->addModelTransformer(new CallbackTransformer(
                function ($originalDescription) {
                    return $originalDescription;
                },
                function ($submittedDescription) {
                    // remove all HTML tags
                    return strip_tags($submittedDescription);
                }
            ))
        ;

    }

    public function getName()
    {
        return 'zikulablocksmodule_textblock';
    }
}