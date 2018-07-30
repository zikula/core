<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\MenuModule\Block\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class MenuType
 */
class MenuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Menu Name',
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('options', TextType::class, [
                'required' => false,
                'invalid_message' => 'Could not json_decode the string you entered.',
                'alert' => ['This must be a json_encoded string of option key-value pairs.' => 'warning']
            ])
        ;
        $builder->get('options')
            ->addModelTransformer(new CallbackTransformer(
                function($text) {
                    return $text;
                },
                function($text) {
                    if (empty($text)) {
                        return '{}';
                    }
                    $json = str_replace("'", '"', $text);
                    if (null === json_decode($json, true)) {
                        throw new TransformationFailedException();
                    }

                    return $json;
                }
            ))
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulamenumodule_menu';
    }
}
