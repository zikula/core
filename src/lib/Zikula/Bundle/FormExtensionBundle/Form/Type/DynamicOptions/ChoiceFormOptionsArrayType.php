<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\FormExtensionBundle\Form\Type\DynamicOptions;

use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ChoiceFormOptionsArrayType extends FormOptionsArrayType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('multiple', CheckboxType::class, [
                'label' => $this->translator->__('Multiple'),
                'required' => false,
            ])
            ->add('expanded', CheckboxType::class, [
                'label' => $this->translator->__('Expanded'),
                'required' => false,
            ])
            ->add('choices', TextType::class, [
                'label' => $this->translator->__('Choices'),
                'help' => $this->translator->__('A comma-delineated list. either "value, value, value" or "key:value, key:value, key:value"'),
            ])
        ;
        $builder->get('choices')
            ->addModelTransformer(new CallbackTransformer(
                function ($choicesArray) {
                    $strings = [];
                    if (isset($choicesArray)) {
                        foreach ($choicesArray as $k => $v) {
                            $strings[] = $k == $v ? $v : $v . ':' . $k;
                        }
                    }

                    return implode(', ', $strings);
                },
                function ($choicesAsString) {
                    $array = explode(',', $choicesAsString);
                    $newArray = [];
                    foreach ($array as $v) {
                        if (strpos($v, ':')) {
                            list($k, $v) = explode(':', $v);
                        } else {
                            $k = $v;
                        }
                        $newArray[trim($v)] = trim($k);
                    }

                    return $newArray;
                }
            ))
        ;
    }
}
